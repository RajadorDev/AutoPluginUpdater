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

use rajadordev\autoupdater\api\plugin\PluginVersionInfo;
use rajadordev\autoupdater\utils\DynamicObject;
use rajadordev\autoupdater\api\exception\NoUpdatesFoundException;
use rajadordev\autoupdater\api\PluginUpdaterChecker;
use rajadordev\autoupdater\api\result\UpdateCheckResult;

/**
 *  NOTE: This class will be used in AsyncTask, so don't use any non-thread safe methods!
 */
abstract class PluginUpdaterAPI extends DynamicObject
{

    const DATA_VERSION = 'version';

    /** @var PluginVersionInfo */
    protected $version;

    public function __construct(
        PluginVersionInfo $pluginVersion
    )
    {
        $this->version = $pluginVersion;
    }

    public function getId() : string 
    {
        return $this->getCurrentVersion()->getId();
    }

    public function getCurrentVersion() : PluginVersionInfo
    {
        return $this->version;
    }

    /**
     * Called before AsyncTask be called, it will never will be called in another threads but can be used to save thread-safe values
     *
     * @param PluginUpdaterChecker $checker
     * @return void
     */
    public function onPrepareCheck(PluginUpdaterChecker $checker)
    {}

    /**
     * Called after AsyncTask has been finished, it can be used to save API values (like ETag from GitHub)
     *
     * @param PluginUpdaterChecker $checker
     * @return void
     */
    public function onPostCheck(PluginUpdaterChecker $checker)
    {}

    /**
     * @param boolean $majorUpdate
     * @param boolean $allowPreReleases
     * @param UpdateCheckResult
     * @throws NoUpdatesFoundException
     */
    abstract public function checkUpdate(bool $majorUpdate, bool $allowPreReleases) : UpdateCheckResult;

    public function jsonSerialize()
    {
        return array_merge(
            parent::jsonSerialize(),
            [
                self::DATA_VERSION => $this->version->jsonSerialize()
            ]
        );
    }

    public static function unserialize(array $data): DynamicObject
    {
        return new self(
            DynamicObject::globalUnserialize(
                $data[self::DATA_VERSION]
            )
        );
    }

}