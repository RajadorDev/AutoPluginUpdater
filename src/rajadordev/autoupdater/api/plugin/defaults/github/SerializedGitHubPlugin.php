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

use rajadordev\autoupdater\api\plugin\PluginSerialized;
use rajadordev\autoupdater\api\plugin\PluginVersionInfo;

class SerializedGitHubPlugin extends PluginSerialized
{

    public function __construct(
        string $fileName, 
        string $content, 
        GitHubReleaseInfo $version
    )
    {
        return parent::__construct($fileName, $content, $version);
    }
    
    /**
     * @return GitHubReleaseInfo
     */
    public function getVersion(): PluginVersionInfo
    {
        return parent::getVersion();
    }

}