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

namespace rajadordev\autoupdater\api\result;

use InvalidArgumentException;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use rajadordev\autoupdater\api\plugin\PluginVersionInfo;
use rajadordev\autoupdater\Loader;
use rajadordev\autoupdater\utils\AutoUpdaterUtils;
use rajadordev\autoupdater\utils\DynamicObject;
use rajadordev\autoupdater\utils\ObjectSerializableList;
use SmartCommand\utils\SingletonTrait;

class UpdateCheckResultsManager extends ObjectSerializableList
{

    use SingletonTrait;

    public static function init(Loader $loader)
    {
        self::setInstance(
            new self(
                $loader->getResultsDir() . 'results.json'
            )
        );
    }

    /** @var array<string,UpdateCheckResult> */
    protected $updateResults = [];

    public function getObjectList(): array
    {
        return $this->updateResults;
    }

    protected function onLoad(DynamicObject $obj)
    {
        /** @var UpdateCheckResult $obj */
        $this->registerResult($obj, false);
    }

    public function registerResult(UpdateCheckResult $result, bool $save) : UpdateCheckResult
    {
        $id = $result->getId();
        if (isset($this->updateResults[$id])) {
            throw new InvalidArgumentException("Record $id is already registered");
        }
        $this->updateResults[$id] = $result;

        if ($save) {
            $this->save();
        }
        return $result;
    }

    public function unregisterResults(Plugin $plugin)
    {
        $pluginId = AutoUpdaterUtils::pluginVersionHash($plugin->getName(), $plugin->getDescription()->getVersion());
        unset($this->updateResults[$pluginId]);
    }


    /**
     * @param Plugin $plugin
     * @return UpdateCheckResult|null
     */
    public function getPluginRecord(Plugin $plugin) 
    {
        $pluginId = AutoUpdaterUtils::pluginVersionHash($plugin->getName(), $plugin->getDescription()->getVersion());
        return $this->updateResults[$pluginId] ?? null;
    }

    public function clearResults(bool $save)
    {
        if (count($this->updateResults)) {
            $this->updateResults = [];
            if ($save) {
                $this->save();
            }
        }
    }

    public function checkUpdatesInstalled()
    {
        $logger = Loader::getInstance()->getLogger();
        $logger->info("Checking plugins updated at the last restart...");
        $mustToSave = false;
        foreach ($this->updateResults as $result) {
            if ($result->isUpdating()) {
                if ($plugin = Server::getInstance()->getPluginManager()->getPlugin($result->getCheckedVersion()->getPluginName())) {
                    if (!$result->getLatestVersion()->getVersion()->isNewestThan(PluginVersionInfo::from($plugin), true)) {
                        $mustToSave = true;
                        $result->setUpdating(false);
                        $logger->notice("Plugin {$plugin->getName()} updated sucefully: \n{$result->getLatestVersion()->getVersion()->infoText()}\n ");
                    }
                }
            }
        }

        if ($mustToSave) {
            $this->save();
        }
    }

}