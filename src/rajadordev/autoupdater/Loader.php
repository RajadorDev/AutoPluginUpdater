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

namespace rajadordev\autoupdater;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use rajadordev\autoupdater\api\AutoUpdaterSettings;
use rajadordev\autoupdater\api\CheckUpdateScheduler;
use rajadordev\autoupdater\api\plugin\defaults\github\GitHubPluginUpdaterAPI;
use rajadordev\autoupdater\api\PluginUpdaterChecker;
use rajadordev\autoupdater\api\result\UpdateCheckResultsManager;
use SmartCommand\utils\SingletonTrait;

class Loader extends PluginBase
{
 
    use SingletonTrait;
 
    public function onLoad()
    {
        self::setInstance($this);
    }

    public function onEnable()
    {
        $dir = $this->getDataFolder();
        $apiDir = $this->getPluginsApiDir();
        $backupDir = $this->getBackupDir();
        $resultsDir = $this->getResultsDir();

        AutoUpdaterSettings::init($this);
        UpdateCheckResultsManager::init($this);
        CheckUpdateScheduler::init();

        foreach ([$dir, $apiDir, $backupDir, $resultsDir] as $systemDir) {
            if (!file_exists($systemDir)) {
                mkdir($systemDir);
            }
        }

        /**
         * Here i will update SmartCommand framework, cause in the future i will create commands using its system
         * So, as SmartCommand can't depend AutoPluginUpdater, i will update it here
         */
        $smartCommand = Server::getInstance()->getPluginManager()->getPlugin('SmartCommand');
        CheckUpdateScheduler::getInstance()->schedule(
            PluginUpdaterChecker::create(
                $smartCommand,
                GitHubPluginUpdaterAPI::createFromPlugin($smartCommand, 'RajadorDev', 'SmartCommand')
            )
        );

        CheckUpdateScheduler::getInstance()->schedule(
            PluginUpdaterChecker::create(
                $this,
                GitHubPluginUpdaterAPI::createFromPlugin($this, 'RajadorDev', 'AutoPluginUpdater')
            )
        );

    }

    public function getBackupDir() : string 
    {
        return $this->getDataFolder() . 'backup' . DIRECTORY_SEPARATOR;
    }

    public function getPluginsApiDir() : string  
    {
        return $this->getDataFolder() . 'api' . DIRECTORY_SEPARATOR;
    }

    public function getResultsDir() : string 
    {
        return $this->getDataFolder() . 'results' . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $identifier
     * @param mixed $defaultValue
     * @param boolean $warnConsole
     * @return mixed
     */
    public function getConfigValue(string $identifier, $defaultValue = null, bool $warnConsole = true)
    {
        $settings = $this->getConfig();
        if ($settings->exists($identifier)) {
            return $settings->get($identifier);
        } else if ($warnConsole) {
            $this->getLogger()->warning("Setting with id $identifier does not found!");
        }
        return $defaultValue;
    }

    public function registerListener(Listener $listener)
    {
        Server::getInstance()->getPluginManager()->registerEvents($listener, $this);
    }

}