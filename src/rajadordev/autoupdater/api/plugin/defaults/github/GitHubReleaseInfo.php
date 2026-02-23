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

namespace rajadordev\autoupdater\api\plugin\defaults\github;

use rajadordev\autoupdater\api\plugin\PluginVersionInfo;
use rajadordev\autoupdater\utils\DynamicObject;
use SmartCommand\utils\CommandUtils;

class GitHubReleaseInfo extends PluginVersionInfo
{

    const DATA_EXTRA = 'extra';

    /** @var array */
    protected $extraData = [];

    public function __construct(
        string $pluginName, 
        string $version,
        array $extraData
    )
    {
        parent::__construct($pluginName, $version);
        $this->extraData = $extraData;
    }

    public function getExtraData() : array 
    {
        return $this->extraData;
    }

    public function getExtraDataValue(string $identifier, $default = null)
    {
        return $this->extraData[$identifier] ?? $default;
    }

    /**
     * @param string $id
     * @param mixed $value
     * @return GitHubReleaseInfo
     */
    public function setExtraDataValue(string $id, $value)
    {
        $this->extraData[$id] = $value;
        return $this;
    }

    /**
     * Returns the plugin serialized
     *
     * @return SerializedGitHubPlugin|null
     */
    public function findPharAsset()
    {
        foreach ($this->getExtraDataValue('assets', []) as $assetData) {
            $fileName = $assetData['name'];
            if (strpos($fileName, '.phar') !== false) {
                $url = $assetData['browser_download_url'];
                $fileData = self::fetchAssetFile($url);
                /** Here i will add a download count cause i'll not fetch it again */
                $assetData['download_count']++;
                return new SerializedGitHubPlugin($fileName, $fileData, $this);
            }
        }
        return null;
    }

    public static function fetchAssetFile(string $url) : string 
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        return curl_exec($curl);
    }

    public function infoText(): string
    {
        $changelog = $this->getExtraDataValue('body', 'No changelog');
        $releaseName = $this->getExtraDataValue('name', $this->getPluginName() . ' ' . $this->version);
        $publishedAt = $this->getExtraDataValue('published_at', '');
        $publishedAt = strtotime($publishedAt);
        $publishedAt = date("d/m/Y h:i", $publishedAt);
        $files = array_map(
            static function (array $assetData) : string  {
                $name = $assetData['name'] ?? 'Unknow';
                return '-    ' . $name . ':  ' . ($assetData['download_count'] ?? '1') . ' downloads'; 
            },
            $this->getExtraDataValue('assets', [])
        );

        $files = implode("\n", $files);

        $changelog = CommandUtils::textLinesWithPrefix(explode("\n", $changelog), true, '-   ');

        $branch = $this->getExtraDataValue('target_commitish', 'none');
        return implode("\n", [
            '- ',
            '- GitHub Release ' . $releaseName,
            '-',
            '- Version: ' . $this->version,
            '-',
            '- Published at: ' . $publishedAt,
            '-',
            '- Branch: ' . $branch,
            '-',
            '- Files:',
            $files,
            '-',
            '- Changelog:',
            '- ',
            $changelog
        ]);
    }

    protected function serializeExtraData(): array
    {
        return array_merge(
            parent::serializeExtraData(),
            [
                self::DATA_EXTRA => $this->extraData
            ]
        );
    }

    public static function unserialize(array $data): DynamicObject
    {
        return new self(
            $data[self::DATA_PLUGIN],
            $data[self::DATA_VERSION],
            $data[self::DATA_EXTRA]
        );
    }

    
}