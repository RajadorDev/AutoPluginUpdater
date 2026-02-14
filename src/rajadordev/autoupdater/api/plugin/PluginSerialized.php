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

class PluginSerialized extends DynamicObject
{

    const DATA_NAME = 'file_name';

    const DATA_VERSION = 'version';

    const DATA_CONTENT = 'file_content';

    /** @var string */
    protected $fileName, $content;

    /** @var PluginVersionInfo */
    protected $version;

    public function __construct(
        string $fileName,
        string $content,
        PluginVersionInfo $version
    )
    {
        $this->fileName = $fileName;
        $this->version = $version;
        $this->content = $content;
    }

    public function getFileName() : string 
    {
        return $this->fileName;
    }

    public function getVersion() : PluginVersionInfo 
    {
        return $this->version;
    }

    public function getFileContent() : string 
    {
        return $this->content;
    }

    public function saveAt(string $path)
    {
        file_put_contents($path . $this->fileName, $this->content);
    }

    public function serializeFileContent() : string 
    {
        return base64_encode($this->content);
    }

    protected function serializeExtraData(): array
    {
        return [
            self::DATA_NAME => $this->fileName,
            self::DATA_VERSION => $this->version->jsonSerialize(),
            self::DATA_CONTENT => $this->serializeFileContent()
        ];
    }

    public static function fileContentFrom(array $data) : string 
    {
        return base64_decode($data[self::DATA_CONTENT]);
    }

    public static function unserialize(array $data): DynamicObject
    {
        $class = static::class;
        return new $class(
            $data[self::DATA_NAME],
            static::fileContentFrom($data),
            DynamicObject::globalUnserialize($data[self::DATA_VERSION])
        );
    }
}