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

class UpdateRecord extends DynamicObject
{

    const DATA_OLD_VERSION = 'old';

    const DATA_NEW_VERSION = 'new';

    const DATA_DATE = 'date';

    /** @var PluginVersionInfo */
    protected $oldVersion, $newVersion;

    /** @var float */
    protected $whenUpdated;

    public function __construct(
        PluginVersionInfo $oldVersion,
        PluginVersionInfo $newVersion,
        float $whenUpdated
    )
    {
        $this->oldVersion = $oldVersion;
        $this->newVersion = $newVersion;
        $this->whenUpdated = $whenUpdated;
    }

    public function getOldVersion() : PluginVersionInfo
    {
        return $this->oldVersion;
    }

    public function getNewVersion() : PluginVersionInfo
    {
        return $this->newVersion;
    }

    public function getUpdateTimestamp() : float 
    {
        return $this->whenUpdated;
    }

    public function getUpdateTimestampFormatted(string $format = 'd/m/Y H:i') : string 
    {
        return date($format, (int) $this->whenUpdated);
    }

    /**
     * @return string[]
     */
    public function info() : array 
    {
        return [
            '',
            '§7Update: §e' . $this->getOldVersion()->getCleanVersion() . ' §8-> §a' . $this->getNewVersion()->getCleanVersion(),
            '',
            '§7Updated at: §9' . $this->getUpdateTimestampFormatted()
        ];
    }

    protected function serializeExtraData(): array
    {
        return [
            self::DATA_OLD_VERSION => $this->oldVersion->jsonSerialize(),
            self::DATA_NEW_VERSION => $this->newVersion->jsonSerialize(),
            self::DATA_DATE => $this->whenUpdated
        ];
    }

    public static function unserialize(array $data): DynamicObject
    {
        return new self(
            DynamicObject::globalUnserialize($data[self::DATA_OLD_VERSION]),
            DynamicObject::globalUnserialize($data[self::DATA_NEW_VERSION]),
            $data[self::DATA_DATE]
        );
    }
}