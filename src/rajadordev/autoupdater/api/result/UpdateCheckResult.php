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
use rajadordev\autoupdater\api\exception\NoUpdatesFoundException;
use rajadordev\autoupdater\api\history\record\UpdateRecord;
use rajadordev\autoupdater\utils\DynamicObject;
use rajadordev\autoupdater\api\plugin\PluginSerialized;
use rajadordev\autoupdater\api\plugin\PluginVersionInfo;

class UpdateCheckResult extends DynamicObject
{

    const DATA_VERSION_CHECKED = 'checked_version';

    const DATA_LATEST_FOUND = 'latest_version_found';

    const DATA_UPDATING = 'in_update_process';

    /** @var PluginVersionInfo */
    protected $checkedVersion;

    /** @var PluginSerialized|null */
    protected $latestVersionFound = null;

    /** @var bool */
    protected $updating = false;

    /**
     * @param PluginVersionInfo $checkedVersion
     * @param PluginSerialized|null $latestVersionFound
     */
    public function __construct(
        PluginVersionInfo $checkedVersion,
        $latestVersionFound,
        bool $updating = false
    )
    {
        $this->checkedVersion = $checkedVersion;
        $this->latestVersionFound = $latestVersionFound;
        $this->updating = $updating;
    }

    public function getId() : string 
    {
        return $this->checkedVersion->getId();
    }

    public function getCheckedVersion() : PluginVersionInfo
    {
        return $this->checkedVersion;
    }

    public function getLatestVersion()
    {
        return $this->latestVersionFound;
    }

    public function needUpdate(Plugin $plugin, bool $compareMajor) : bool 
    {
        if (((string) $plugin->getDescription()->getVersion()) != $this->checkedVersion->getFullVersion()) {
            throw new InvalidArgumentException("Can't compare different check results!");
        }

        if ($this->latestVersionFound) {
            return $this->latestVersionFound->getVersion()->isNewestThan(
                PluginVersionInfo::from($plugin),
                $compareMajor
            );
        }
        return false;
    }

    public function install()
    {
        if ($this->latestVersionFound) {
            $this->latestVersionFound->saveAt(
                Server::getInstance()->getDataPath() . 'plugins' . DIRECTORY_SEPARATOR
            );
        } else {
            throw new NoUpdatesFoundException("There is no update to {$this->getId()}");
        }
    }

    public function setUpdating(bool $set)
    {
        $this->updating = $set;
    }

    public function isUpdating() : bool 
    {
        return $this->updating;
    }

    public function createHistory(PluginVersionInfo $from, PluginVersionInfo $to) : UpdateRecord
    {
        return new UpdateRecord(
            $from,
            $to,
            microtime(true)
        );
    }

    protected function serializeExtraData(): array
    {
        return [
            self::DATA_VERSION_CHECKED => $this->checkedVersion->jsonSerialize(),
            self::DATA_LATEST_FOUND => $this->latestVersionFound ? $this->latestVersionFound->jsonSerialize() : null,
            self::DATA_UPDATING => $this->updating
        ];
    }

    public static function unserialize(array $data): DynamicObject
    {
        $latestVersion = $data[self::DATA_LATEST_FOUND];
        return new self(
            DynamicObject::globalUnserialize($data[self::DATA_VERSION_CHECKED]),
            is_null($latestVersion) ? null : DynamicObject::globalUnserialize($latestVersion),
            $data[self::DATA_UPDATING]
        );
    }
}