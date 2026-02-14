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

use pocketmine\plugin\Plugin;
use rajadordev\autoupdater\api\exception\NoUpdatesFoundException;
use rajadordev\autoupdater\api\plugin\PluginUpdaterAPI;
use rajadordev\autoupdater\api\plugin\PluginVersionInfo;
use rajadordev\autoupdater\api\result\UpdateCheckResult;
use rajadordev\autoupdater\Loader;
use rajadordev\autoupdater\utils\AutoUpdaterUtils;

class PluginUpdaterChecker 
{

    /** @var Plugin */
    protected $plugin;

    /** @var PluginUpdaterAPI */
    protected $api;

    /** @var string */
    protected $pluginFile;

    /** @var array<string,mixed> */
    protected $extraRecords = [];

    /**
     * @param Plugin $plugin
     * @param PluginUpdaterAPI $api
     * @param string|null $filePath
     * @return PluginUpdaterChecker
     */
    public static function create(Plugin $plugin, PluginUpdaterAPI $api, $filePath = null) : PluginUpdaterChecker
    {
        return new self($plugin, $api, $filePath);
    }

    public function __construct(
        Plugin $plugin,
        PluginUpdaterAPI $api,
        string $pluginFile = null
    )
    {
        $this->plugin = $plugin;
        $this->api = $api;
        $this->pluginFile = $pluginFile ?? AutoUpdaterUtils::getPathFromPlugin($plugin);
        $this->loadExtraDataRecords();
    }

    public function getId() : string 
    {
        return $this->getVersion()->getId();
    }

    public function getVersion() : PluginVersionInfo
    {
        return $this->api->getCurrentVersion();
    }

    public function getPlugin() : Plugin
    {
        return $this->plugin;
    }

    public function getPluginName() : string 
    {
        return $this->plugin->getName();
    }

    public function getApi() : PluginUpdaterAPI
    {
        return $this->api;
    }

    public function isPhar() : bool 
    {
        return AutoUpdaterUtils::isPhar($this->plugin);
    }

    public function getPharFile() : string 
    {
        return AutoUpdaterUtils::getPathFromPlugin($this->plugin);
    }

    public function getPharRealPath() : string 
    {
        $path = $this->getPharFile();
        $path = str_replace('phar://', '', $path);
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function canCheckNewUpdates() : bool 
    {
        return true;
    }

    public function getExtraApiValue(string $id, $default = null)
    {
        return $this->extraRecords[$id] ?? $default;
    }

    public function setExtraApiValue(string $id, $value, bool $save = true) 
    {
        if (!isset($this->extraRecords[$id]) || $this->extraRecords[$id] != $value) {
            $this->extraRecords[$id] = $value;
            if ($save) {
                $this->saveExtraRecords();
            }
        }
    }

    public function getExtraRecordsPath() : string 
    {
        return Loader::getInstance()->getPluginsApiDir() . str_replace([' ', ':'], ['_', '_'], $this->getId() . '.json');
    }

    public function setExtraData(array $data)
    {
        $this->extraRecords = $data;
        $this->saveExtraRecords();
    }

    public function backup(string $dir) : string
    {
        $backupContent = file_get_contents($this->getPharRealPath());
        file_put_contents($fullPath = $dir . $this->plugin->getName() . '-' . $this->getVersion()->getFullVersion() . '-backup.phar', $backupContent);
        return $fullPath;
    }

    protected function saveExtraRecords()
    {
        $path = $this->getExtraRecordsPath();
        file_put_contents($path, json_encode($this->extraRecords));
    }

    protected function loadExtraDataRecords()
    {
        if (file_exists($path = $this->getExtraRecordsPath())) {
            $content = file_get_contents($path);
            $this->extraRecords = json_decode($content, true);
        }
    }
    
    /**
     * @param PluginUpdaterAPI $api
     * @param boolean $majorUpdate
     * @param boolean $allowPreReleases
     * @return UpdateCheckResult
     * @throws NoUpdatesFoundException
     */
    public static function syncUpdate(PluginUpdaterAPI $api, bool $majorUpdate, bool $allowPreReleases) : UpdateCheckResult
    {
        return $api->checkUpdate($majorUpdate, $allowPreReleases);
    }


}