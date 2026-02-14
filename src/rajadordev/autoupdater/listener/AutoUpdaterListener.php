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

namespace rajadordev\autoupdater\listener;

use pocketmine\event\Listener;
use pocketmine\event\plugin\PluginDisableEvent;
use rajadordev\autoupdater\api\CheckUpdateScheduler;
use rajadordev\autoupdater\utils\AutoUpdaterUtils;

final class AutoUpdaterListener implements Listener
{

    /** @var CheckUpdateScheduler */
    protected $scheduler;

    public function __construct(
        CheckUpdateScheduler $scheduler
    )
    {
        $this->scheduler = $scheduler;
    }

    /**
     * @priority MONITOR
     */
    public function deletePlugin(PluginDisableEvent $event) {
        $plugin = $event->getPlugin();
        if ($this->scheduler->isWaitingToDelete($plugin)) {
            if (AutoUpdaterUtils::isPhar($plugin)) {
                unlink(AutoUpdaterUtils::getPathFromPlugin($plugin));
            }
        }
    }
}