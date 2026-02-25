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

namespace rajadordev\autoupdater\api\history;

use InvalidArgumentException;
use pocketmine\plugin\Plugin;
use rajadordev\autoupdater\api\history\record\PluginUpdatesList;
use rajadordev\autoupdater\Loader;
use rajadordev\autoupdater\utils\DynamicObject;
use rajadordev\autoupdater\utils\ObjectSerializableList;
use SmartCommand\utils\SingletonTrait;

final class UpdatesHistoryManager extends ObjectSerializableList
{

    use SingletonTrait;

    /** @var array<string,PluginUpdatesList> */
    protected $updates = [];

    public static function init(Loader $plugin)
    {
        $instance = new self($plugin->getResultsDir() . 'updates.json');
        self::setInstance($instance);
    }

    public function getObjectList(): array
    {
        return $this->updates;
    }

    protected function onLoad(DynamicObject $obj)
    {
        $this->registerUpdates($obj, false);
    }

    protected function registerUpdates(PluginUpdatesList $list, bool $save) 
    {
        if (isset($this->updates[$plugin = $list->getPluginName()])) {
            throw new InvalidArgumentException("Plugin $plugin is already registered in updates list");
        }
        $this->updates[$plugin] = $list;

        if ($save) {
            $this->save();
        }
    }

    public function getUpdatesFrom(Plugin $plugin) 
    {
        return $this->updates[$plugin->getName()] ?? null;
    }

    public function getOrCreateUpdatesList(Plugin $plugin) : PluginUpdatesList
    {
        $name = $plugin->getName();
        if (isset($this->updates[$name])) {
            return $this->updates[$name];
        }
        $list = PluginUpdatesList::createEmpty($name);
        $this->registerUpdates($list, false);
        return $list;
    }


}