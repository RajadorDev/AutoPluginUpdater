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

namespace rajadordev\autoupdater\utils;

use pocketmine\plugin\Plugin;
use pocketmine\Server;
use rajadordev\autoupdater\api\plugin\PluginVersionInfo;
use ReflectionClass;

class AutoUpdaterUtils 
{

    const CLEARED_VERSION_CHARS = '1234567890.';

    /**
     * I created it to remove every extra version text (like: 1.0.0 Beta -> 1.0.0 only)
     *
     * @param string $version
     * @return string
     */
    public static function clearVersion(string $version) : string 
    {
        $clearedVersionChars = '';
        foreach (str_split($version) as $char) {
            if (strpos(self::CLEARED_VERSION_CHARS, $char) !== false) {
                $clearedVersionChars .= $clearedVersionChars;
            }
        }
        return $clearedVersionChars;
    }

    /**
     * It will remove major version from string like:
     * Input: "1.0.0" 
     * Output: "0.0"
     * 
     * @param string $version
     * @return string
     */
    public static function removeMajorVersion(string $version) : string 
    {
        $splitedVersion = explode('.', $version);
        array_shift($splitedVersion);
        return implode('.', $splitedVersion);
    }

    /**
     * @param string $name
     * @param string $version
     * @return string
     */
    public static function pluginVersionHash(string $name, string $version) : string 
    {
        $version = self::clearVersion($version);
        return "$name:$version";
    }

    /**
     * @param PluginVersionInfo|string $input
     * @return Plugin|null
     */
    public static function fectchPlugin($input) 
    {
        if ($input instanceof PluginVersionInfo) {
            $input = $input->getPluginName();
        }

        assert(is_string($input));
        return Server::getInstance()->getPluginManager()->getPlugin($input);
    }

    public static function getPathFromPlugin(Plugin $plugin) : string 
    {
        $classFile = (new ReflectionClass($plugin))->getFileName();
        $classFile = explode('/src', $classFile);
        return array_shift($classFile);
    }

    public static function isPhar(Plugin $plugin) : bool 
    {
        return strpos(self::getPathFromPlugin($plugin), 'phar://') === 0;
    }
    
}