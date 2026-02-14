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

namespace rajadordev\autoupdater\api\task;

use pocketmine\Server;
use rajadordev\autoupdater\api\CheckUpdateScheduler;
use rajadordev\autoupdater\utils\async\AsyncPromiseTask;
use rajadordev\autoupdater\api\PluginUpdaterChecker;
use rajadordev\autoupdater\utils\DynamicObject;
use rajadordev\autoupdater\utils\promise\Promise;

class AsyncUpdatesCheckTask extends AsyncPromiseTask
{

    /** @var string */
    protected $apis;

    /** @var bool */
    protected $majorUpdate, $allowPreReleases, $debugEnabled;

    /**
     * @param PluginUpdaterChecker[] $updatersCheckers
     * @param boolean $majorUpdate
     * @param boolean $allowPreReleases
     * @param boolean $debugEnabled
     */
    public static function scheduleUpdate(array $updatersCheckers, bool $majorUpdate, bool $allowPreReleases, bool $debugEnabled) : Promise
    {
        $task = new self($updatersCheckers, $majorUpdate, $allowPreReleases, $debugEnabled);
        static::schedule($task);
        return $task->getPromise();
    }

    /**
     * @param PluginUpdaterChecker[] $updatersCheckers
     * @param boolean $majorUpdate
     * @param boolean $allowPreReleases
     * @param boolean $debugEnabled
     */
    public function __construct(array $updatersCheckers, bool $majorUpdate, bool $allowPreReleases, bool $debugEnabled)
    {
        $apis = [];
        foreach ($updatersCheckers as $checker) {
            $api = $checker->getApi();
            $api->onPrepareCheck($checker);
            $apis[] = $api;
        }
        $this->apis = json_encode($apis);
        $this->majorUpdate = $majorUpdate;
        $this->allowPreReleases = $allowPreReleases;
        $this->debugEnabled = $debugEnabled;
        return parent::__construct();
    }

    public function onRun()
    {
        $apis = json_decode($this->apis, true);
        $this->setResult(
            json_encode(
                CheckUpdateScheduler::syncUpdateAll($apis, $this->majorUpdate, $this->allowPreReleases, $this->debugEnabled)
            )
        );
    }

    public function onCompletion(Server $server)
    {
        $result = $this->getResult();
        $result = json_decode($result, true);
        $result = array_map(
            static function ($eachResult) {
                if (is_array($eachResult)) {
                    return array_map(
                        static function (array $objectSerialized) : DynamicObject {
                            return DynamicObject::globalUnserialize($objectSerialized);
                        },
                        $eachResult
                    );
                }
                return $eachResult;
            },
            $result
        );
        $this->getResolver()->resolve($result);
    }

}