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

namespace rajadordev\autoupdater\api\history\record;

use rajadordev\autoupdater\api\plugin\PluginVersionInfo;
use rajadordev\autoupdater\utils\DynamicObject;

class PluginUpdatesList extends DynamicObject
{

    const DATA_PLUGIN_NAME = 'plugin';

    const DATA_RECORDS = 'records';

    /** @var string */
    protected $pluginName;

    /** @var UpdateRecord[] */
    protected $records = [];

    public function __construct(
        string $pluginName,
        array $updates
    )
    {
        $this->pluginName = $pluginName;
        $this->records = $updates;   
    }

    public static function createEmpty(string $pluginName) : PluginUpdatesList
    {
        return new self($pluginName, []);
    }

    public function getPluginName() : string 
    {
        return $this->pluginName;
    }

    public function getRecords() : array 
    {
        return $this->records;
    }

    public function getUpdatesCount() : int 
    {
        return count($this->records);
    }

    public function push(UpdateRecord $record)
    {
        $this->records[] = $record;
    }

    public function getUpdateInfo(PluginVersionInfo $version)
    {
        foreach ($this->records as $record) {
            if ($record->getNewVersion()->equals($version)) {
                return $record;
            }
        }
        return null;
    }

    protected function serializeExtraData(): array
    {
        return [
            self::DATA_PLUGIN_NAME => $this->pluginName,
            self::DATA_RECORDS => DynamicObject::serializeAll($this->records)
        ];
    }

    public static function unserialize(array $data): DynamicObject
    {
        return new self(
            $data[self::DATA_PLUGIN_NAME],
            DynamicObject::unserializeAll($data[self::DATA_RECORDS])
        );
    }

}