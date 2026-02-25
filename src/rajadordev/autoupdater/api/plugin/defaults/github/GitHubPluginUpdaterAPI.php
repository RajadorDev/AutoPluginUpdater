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

use Exception;
use pocketmine\plugin\Plugin;
use rajadordev\autoupdater\api\exception\NoUpdatesFoundException;
use rajadordev\autoupdater\api\plugin\PluginUpdaterAPI;
use rajadordev\autoupdater\api\plugin\PluginVersionInfo;
use rajadordev\autoupdater\api\PluginUpdaterChecker;
use rajadordev\autoupdater\api\result\UpdateCheckResult;
use rajadordev\autoupdater\utils\DynamicObject;

class GitHubPluginUpdaterAPI extends PluginUpdaterAPI
{

    const DATA_GITHUB_LAST_REQUEST = 'last_request_id';

    const DATA_REPOSITORY = 'repository';

    /** @var GitHubRepository */
    protected $repository;

    /** @var string|null Used to ignore github rate-limit */
    protected $lastRequestIdentifier = null;

    /**
     * @param PluginVersionInfo $pluginVersion
     * @param GitHubRepository $repository
     * @param string|null $lastRequestIdentifier
     */
    public function __construct(
        PluginVersionInfo $pluginVersion,
        GitHubRepository $repository,
        $lastRequestIdentifier = null
    )
    {
        $this->repository = $repository;
        $this->lastRequestIdentifier = $lastRequestIdentifier;
        parent::__construct($pluginVersion);
    }

    public static function createFromPlugin(Plugin $plugin, string $author, string $repositoryName) : GitHubPluginUpdaterAPI
    {
        return new self(
            PluginVersionInfo::from($plugin),
            new GitHubRepository($author, $repositoryName)
        );
    }

    public function getRepository() : GitHubRepository
    {
        return $this->repository;
    }

    /**
     * @param boolean $majorUpdate
     * @param boolean $allowPreReleases
     * @return NoUpdatesFoundException
     */
    public function checkUpdate(bool $majorUpdate, bool $allowPreReleases) : UpdateCheckResult
    {

        $currentVersionInfo = $this->getCurrentVersion();

        $pluginName = $currentVersionInfo->getPluginName();

        $header = [];
        if ($this->lastRequestIdentifier) {
            $header[] = "If-None-Match: $this->lastRequestIdentifier";
        }
        $header[] = 'User-Agent: AutoPluginUpdater/1.0';

        $outHeaders = [];
        $repositoryUrl = $this->repository->getReleasesUrl();
        $curl = curl_init($repositoryUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, static function ($resource, $headerLine) use (&$outHeaders) : int {
            $length = strlen($headerLine);
            $headerInfo = explode(':', $headerLine, 2);
            if (count($headerInfo) <= 1) {
                return $length;
            }
            $headerKey = $headerInfo[0];
            $headerValue = $headerInfo[1];
            $outHeaders[$headerKey] = $headerValue;
            return $length;
        });
        $result = curl_exec($curl);
        $resultCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (!is_string($result) || !in_array($resultCode, [200, 201, 202, 204, 304])) {
            $errorText = curl_error($curl);
            if ($errorText == '') {
                $errorText = 'No curl error';
            }
            if (!is_string($result)) {
                $result = 'None';
            }
            throw new Exception("Error while trying to fetch $repositoryUrl ($resultCode)\n Response: $result :\n" . $errorText);
        } else if ($resultCode == 304) {
            throw new NoUpdatesFoundException("GitHub returns NOT modified code 304", 304);
        }

        $releasesList = json_decode($result, true);

        $latestVersion = $currentVersionInfo;

        foreach ($releasesList as $release) {

            if (!$allowPreReleases && $release['prerelease']) {
                continue;
            }

            $tag = $release['tag_name'];
            
            $releaseInfo = new GitHubReleaseInfo($pluginName, $tag, $release);

            if ($releaseInfo->isNewestThan($latestVersion, $majorUpdate)) {
                $latestVersion = $releaseInfo;
            }
        }

        $requestOutId = trim($outHeaders['ETag']);

        $this->lastRequestIdentifier = $requestOutId;

        if ($latestVersion !== $currentVersionInfo) {
            assert($latestVersion instanceof GitHubReleaseInfo);
            return new UpdateCheckResult(
                $currentVersionInfo,
                $latestVersion->findPharAsset()
            );
        } else {
            return new UpdateCheckResult(
                $latestVersion,
                null
            );
        }
    }

    public function onPrepareCheck(PluginUpdaterChecker $checker)
    {
        /** @var GitHubReleaseInfo */
        $version = $this->getCurrentVersion();
        $this->lastRequestIdentifier = $checker->getExtraApiValue($version->getLastRequestId());
    }

    public function onPostCheck(PluginUpdaterChecker $api)
    {
        /** @var GitHubReleaseInfo */
        $version = $this->getCurrentVersion();
        $api->setExtraApiValue($version->getLastRequestId(), $this->lastRequestIdentifier, true);
    }

    protected function serializeExtraData(): array
    {
        return [
            self::DATA_REPOSITORY => $this->repository->jsonSerialize(),
            self::DATA_GITHUB_LAST_REQUEST => $this->lastRequestIdentifier
        ];
    }

    public static function unserialize(array $data): DynamicObject
    {
        return new self(
            DynamicObject::globalUnserialize($data[self::DATA_VERSION]),
            DynamicObject::globalUnserialize($data[self::DATA_REPOSITORY]),
            $data[self::DATA_GITHUB_LAST_REQUEST]
        );
    }
}