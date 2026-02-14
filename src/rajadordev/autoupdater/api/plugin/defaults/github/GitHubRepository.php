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

use rajadordev\autoupdater\utils\DynamicObject;

class GitHubRepository extends DynamicObject
{

    const DATA_AUTHOR = 'author';

    const DATA_REPO = 'repository';

    /** @var string */
    protected $author, $repositoryName;

    /** @var string */
    protected $repositoryApiUrl;

    public function __construct(
        string $authorName,
        string $repositoryName
    )
    {
        $this->author = $authorName;
        $this->repositoryName = $repositoryName;
        $this->repositoryApiUrl = "https://api.github.com/repos/{$authorName}/{$repositoryName}/";
    }

    public function getReleasesUrl() : string 
    {
        return $this->repositoryApiUrl . 'releases';
    }

    protected function serializeExtraData(): array
    {
        return [
            self::DATA_AUTHOR => $this->author,
            self::DATA_REPO => $this->repositoryName
        ];
    }

    public static function unserialize(array $data): DynamicObject
    {
        return new self(
            $data[self::DATA_AUTHOR],
            $data[self::DATA_REPO]
        );
    }
}