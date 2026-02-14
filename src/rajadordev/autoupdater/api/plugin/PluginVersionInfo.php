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

namespace rajadordev\autoupdater\api\plugin;

use pocketmine\plugin\Plugin;
use rajadordev\autoupdater\utils\AutoUpdaterUtils;
use rajadordev\autoupdater\utils\DynamicObject;

class PluginVersionInfo extends DynamicObject
{

    const DATA_PLUGIN = 'plugin_name';

    const DATA_VERSION = 'version';

    /** @var string */
    protected $name, $version, $id;

    public function __construct(
        string $pluginName,
        string $version
    )
    {
        $this->name = $pluginName;
        $this->version = $version;
        $this->id = AutoUpdaterUtils::pluginVersionHash($pluginName, $version);
    }

    public static function from(Plugin $plugin) : PluginVersionInfo
    {
        return new self($plugin->getName(), (string) $plugin->getDescription()->getVersion());
    }

    public function getId() : string 
    {
        return $this->id;
    }

    public function getPluginName() : string 
    {
        return $this->name;
    }

    /**
     * It will return the full version string (including "beta|alpha" text)
     * @return string
     */
    public function getFullVersion() : string 
    {
        return $this->version;
    }

    /**
     * It will remove every non version chars (like: beta|alpha)
     * The return will be aways: "MAJOR.MINOR.PATCH"
     * @return string
     */
    public function getCleanVersion() : string 
    {
        return AutoUpdaterUtils::clearVersion($this->getFullVersion());
    }

    /**
     * It will return only MINOR and PATH version
     * @return string
     */
    public function getCleanVersionWithoutMajor() : string
    {
        return AutoUpdaterUtils::removeMajorVersion($this->version);
    }

    public function isNewestThan(PluginVersionInfo $with, bool $compareMajor) : bool
    {
        $versionString = $compareMajor ? $this->getCleanVersion() : $this->getCleanVersionWithoutMajor();
        $withVersion = $compareMajor ? $with->getCleanVersion() : $with->getCleanVersionWithoutMajor();
        return version_compare($versionString, $withVersion, '>');
    }

    public function infoText() : string 
    {
        return 'Version: ' . $this->getPluginName() . ' ' . $this->getFullVersion();
    }

    protected function serializeExtraData(): array
    {
        return [
            self::DATA_PLUGIN => $this->name,
            self::DATA_VERSION => $this->version
        ];
    }

    public static function unserialize(array $data): DynamicObject
    {
        return new self(
            $data[self::DATA_PLUGIN],
            $data[self::DATA_VERSION]
        );
    }
}