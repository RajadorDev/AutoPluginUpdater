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

use pocketmine\utils\Config;
use rajadordev\autoupdater\Loader;
use SmartCommand\utils\SingletonTrait;

class AutoUpdaterSettings 
{

    const AUTO_UPDATE = 'auto-update-enabled';

    const CHECK_STARTUP = 'check-startup';

    const ASYNC_CHECK = 'async-check';

    const MAJOR_UPDATES = 'major-updates';

    const AUTO_BACKUP = 'auto-backup';

    const AUTO_UPDATE_RESTART = 'restart-when-update';

    const ALLOW_PRE_RELEASES = 'allow-pre-releases';

    use SingletonTrait;

    /** @var Config */
    protected $config;

    public static function init(Loader $plugin)
    {
        $plugin->saveResource('config.yml');
        new self($plugin->getConfig());
    }

    public function __construct(Config $config)
    {
        self::setInstance($this);
        $this->config = $config;
    }

    public function isAsyncCheckEnabled() : bool
    {
        return $this->getConfigValue(self::ASYNC_CHECK, true);
    }

    public function isStartupCheckEnabled() : bool 
    {
        return $this->getConfigValue(self::CHECK_STARTUP, true);
    }

    public function isAutoUpdateEnabled() : bool 
    {
        return $this->getConfigValue(self::AUTO_UPDATE, true);
    }

    public function isMajorUpdateEnabled() : bool 
    {
        return $this->getConfigValue(self::MAJOR_UPDATES, false);
    }

    public function isAutoBackupEnabled() : bool 
    {
        return $this->getConfigValue(self::AUTO_BACKUP, true);
    }

    public function isAutoRestartWhenUpdated() : bool 
    {
        return $this->getConfigValue(self::AUTO_UPDATE_RESTART, true);
    }

    public function allowPreReleases() : bool 
    {
        return $this->getConfigValue(self::ALLOW_PRE_RELEASES, false);
    }

    /**
     * @param string $id
     * @param mixed $default
     * @param boolean $warnConsoleWhenFail
     * @return mixed
     */
    public function getConfigValue(string $id, $default, bool $warnConsoleWhenFail = true) 
    {
        if ($this->config->exists($id)) {
            return $this->config->get($id);
        } else if ($warnConsoleWhenFail) {
            Loader::getInstance()->getLogger()->warning("Setting with id \"$id\" does not found");
        }
        return $default;
    }

}