<?php

declare (strict_types=1);
 
/***
 *   
 * Rajador Developer
 * 
 * ▒█▀▀█ ░█▀▀█ ░░░▒█ ░█▀▀█ ▒█▀▀▄ ▒█▀▀▀█ ▒█▀▀█ 
 * ▒█▄▄▀ ▒█▄▄█ ░▄░▒█ ▒█▄▄█ ▒█░▒█ ▒█░░▒█ ▒█▄▄▀ 
 * ▒█░▒█ ▒█░▒█ ▒█▄▄█ ▒█░▒█ ▒█▄▄▀ ▒█▄▄▄█ ▒█░▒█
 * 
 * GitHub: https://github.com/rajadordev
 * 
 * Discord: rajadortv
 * 
 * 
**/ 

namespace rajadordev\autoupdater\api;

use InvalidArgumentException;
use pocketmine\plugin\Plugin;
use Throwable;
use pocketmine\Server;
use rajadordev\autoupdater\Loader;
use SmartCommand\utils\SingletonTrait;
use rajadordev\autoupdater\utils\ClosureTask;
use rajadordev\autoupdater\utils\DynamicObject;
use rajadordev\autoupdater\api\plugin\PluginUpdaterAPI;
use rajadordev\autoupdater\api\exception\NoUpdatesFoundException;
use rajadordev\autoupdater\api\logger\AutoPluginUpdaterLogger;
use rajadordev\autoupdater\api\result\UpdateCheckResult;
use rajadordev\autoupdater\api\result\UpdateCheckResultsManager;
use rajadordev\autoupdater\api\task\AsyncUpdatesCheckTask;
use rajadordev\autoupdater\listener\AutoUpdaterListener;
use rajadordev\autoupdater\utils\Performance;

class CheckUpdateScheduler 
{

    use SingletonTrait;

    /** @var array<string,PluginUpdaterChecker> */
    protected $scheduled = [];

    /** @var string[] */
    protected $pluginsToDelete = [];

    /** @var boolean */
    protected $debugEnabled;

    /** @var AutoUpdaterSettings */
    protected $settings;

    /** @var PluginLogger */
    protected $logger;

    /** @var AutoPluginUpdaterLogger */
    protected $autoUpdaterLogger;

    /** @var Loader */
    protected $plugin;

    public static function init() {
        self::setInstance(new self);
    }

    public function __construct()
    {
        $server = Server::getInstance();
        $this->debugEnabled = ((int) $server->getProperty('debug.level', 1)) > 1;
        $this->settings = AutoUpdaterSettings::getInstance();
        $this->plugin = Loader::getInstance();
        $this->logger = $this->plugin->getLogger();
        $this->autoUpdaterLogger = AutoPluginUpdaterLogger::getInstance();

        $this->plugin->registerListener(new AutoUpdaterListener($this));

        if ($this->settings->isStartupCheckEnabled()) {
            ClosureTask::scheduleDelayed(
                5,
                function () {
                    UpdateCheckResultsManager::getInstance()->checkUpdatesInstalled();
                    $checkers = $this->getUpdatableCheckers(true);

                    if (count($checkers) == 0) {
                        return;
                    }

                    $majorUpdate = $this->settings->isMajorUpdateEnabled();
                    $allowPreReleases = $this->settings->allowPreReleases();
                    $debugEnabled = $this->debugEnabled;
                    if ($this->settings->isAsyncCheckEnabled()) {
                        $started = Performance::start();
                        $this->logger->info("Starting to check updates in background...");
                        AsyncUpdatesCheckTask::scheduleUpdate($checkers, $majorUpdate, $allowPreReleases, $debugEnabled)->then(
                            function (array $results) use ($started) {
                                $this->logger->debug("Async updates check finished in {$started->finish()->getFormattedResult()}");
                                $this->processCheckResult($results);
                            }
                        );
                    } else {
                        $this->logger->info("Starting to check updates in sync mode...");
                        $apis = [];
                        foreach ($checkers as $checker) {
                            $api = $checker->getApi();
                            $api->onPrepareCheck($checker);
                            $apis[] = $api;
                        }
                        $results = self::syncUpdateAll($apis, $majorUpdate, $allowPreReleases, $debugEnabled);
                        $this->processCheckResult($results);
                    }
                }
            );
        }
    }

    public function getUpdater(string $identifier)
    {
        return $this->scheduled[$identifier] ?? null;
    }

    public function schedule(PluginUpdaterChecker $checker)
    {
        $id = $checker->getId();
        if (isset($this->scheduled[$id])) {
            throw new InvalidArgumentException("Update check for the plugin $id is already registered!");
        }

        $this->scheduled[$id] = $checker;
    }

    /**
     * @param bool $alertSkips
     * @return PluginUpdaterChecker[]
     */
    public function getUpdatableCheckers(bool $alertSkips) : array 
    {
        return array_filter(
            $this->scheduled,
            function (PluginUpdaterChecker $checker) use ($alertSkips) : bool {
                if (!$checker->isPhar()) {
                    if ($alertSkips) {
                        $this->autoUpdaterLogger->alert("Can't check updates of {$checker->getPlugin()->getName()}: Plugin isn't phar format!");
                    }
                    return false;
                } else if (!$checker->canCheckNewUpdates()) {
                    if ($alertSkips) {
                        $this->logger->debug("Skipping updates checks from {$checker->getPlugin()->getName()}...");
                    }
                    return false;
                }
                return true;
            }
        );
    }

    /**
     * @param array<string,array{api:PluginUpdaterAPI,result:UpdateCheckResult}|string[]> $results
     * @return void
     */
    public function processCheckResult(array $results)
    {
        $resultsManager = UpdateCheckResultsManager::getInstance();
        $compareMajor = $this->settings->isMajorUpdateEnabled();
        $shouldSaveResults = false;
        $makeBackup = $this->settings->isAutoBackupEnabled();
        $pluginsUpdated = [];
        $someUpdateFound = false;
        foreach ($results as $pluginIdentifier => $resultData) {
            if (is_string($resultData)) {
                $this->autoUpdaterLogger->error("Error while check updates to {$pluginIdentifier}: {$resultData}");
                continue;
            }

            $api = $resultData['api'];
            /** @var UpdateCheckResult */
            $result = $resultData['result'];

            if ($updater = $this->getUpdater($pluginIdentifier)) {
                $plugin = $updater->getPlugin();
                $resultsManager->unregisterResults($plugin);
                $resultsManager->registerResult($result, false);
                $shouldSaveResults = true;
                $api->onPostCheck($updater);
                if ($result->needUpdate($updater->getPlugin(), $compareMajor)) {
                    
                    $someUpdateFound = true;

                    if ($makeBackup) {
                        $this->autoUpdaterLogger->info("Creating backup for {$pluginIdentifier} plugin...");
                        $path = $updater->backup($this->plugin->getBackupDir());
                        $this->autoUpdaterLogger->notice("Backup created in {$path}");
                    }

                    if ($this->settings->isAutoUpdateEnabled()) 
                    {
                        $latest = $result->getLatestVersion();
                        $this->autoUpdaterLogger->notice("Installing latest {$updater->getPluginName()} version...");
                        $latest->saveAt(Server::getInstance()->getDataPath() . 'plugins' . DIRECTORY_SEPARATOR);
                        $this->autoUpdaterLogger->notice("Plugin {$latest->getFileName()} installed sucefully");
                        $result->setUpdating(true);
                        $pluginsUpdated[] = $result;
                    } else {
                        $this->autoUpdaterLogger->notice("New {$updater->getPluginName()} version found: \n{$latest->getVersion()->infoText()}\n ");
                    }

                }
            } else {
                $this->autoUpdaterLogger->error("Plugin {$pluginIdentifier} does not found!");
            }
        }

        if ($shouldSaveResults) {
            $resultsManager->save();
        }

        $updateCount = count($pluginsUpdated);
        if ($updateCount > 0) {
            $this->logger->info("{$updateCount} plugins was updated.");
            if ($this->settings->isAutoRestartWhenUpdated()) {
                $this->autoUpdaterLogger->notice("Restarting to install {$updateCount} plugins...");
                $text = $this->settings->getConfigValue('restart-screen-text', 'Updating...');
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    $player->close('', $text);
                }
                Server::getInstance()->shutdown();
            }
        } else if (!$someUpdateFound) {
            $this->autoUpdaterLogger->info("Everything is up to date.");
        }
    }

    /**
     * @param PluginUpdaterAPI[]|array[] $list
     * @param bool $majorUpdate
     * @param bool $allowPreReleases
     * @param bool $debugEnabled
     * @return array<string,{api:array,result:array}|string[]>
     */
    public static function syncUpdateAll(array $list, bool $majorUpdate, bool $allowPreReleases, bool $debugEnabled) : array
    {
        $results = [];
        foreach ($list as $api) {
            if (is_array($api)) {
                $api = DynamicObject::globalUnserialize($api);
            }

            assert($api instanceof PluginUpdaterAPI);

            $identifier = $api->getId();

            try {

                $result = $api->checkUpdate($majorUpdate, $allowPreReleases);

                $results[$identifier] = ['api' => $api->jsonSerialize(), 'result' => $result->jsonSerialize()];

            } catch (NoUpdatesFoundException $error) {
                continue;
            } catch (Throwable $error) {
                if ($debugEnabled) {
                    $error = (string) $error;
                } else {
                    $error = $error->getMessage();
                }
                $results[$identifier] = $error;
            }
        }
        return $results;
    }

    public function isWaitingToDelete(Plugin $plugin) : bool 
    {
        return in_array($plugin->getName(), $this->pluginsToDelete);
    }

    public function setWaitingToDelete(Plugin $plugin) 
    {
        $this->pluginsToDelete[] = $plugin->getName();
    }

    
}