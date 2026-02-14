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

namespace rajadordev\autoupdater\utils\async;

use pocketmine\Server;
use Throwable;
use rajadordev\autoupdater\utils\promise\Promise;
use rajadordev\autoupdater\utils\promise\PromiseResolver;

/**
 * This class will use Promise system and automatically try to converts strings to json_decode
 */
abstract class AsyncPromiseTask extends RealAsyncTask
{

    const VAR_RESOLVER_ID = 'PromiseResolver';

    const INDEX_ERROR_RESULT = 'error';

    public function __construct()
    {
        $this->saveToThreadStore(self::VAR_RESOLVER_ID, new PromiseResolver);
    }

    protected function getResolver() : PromiseResolver
    {
        return $this->getFromThreadStore(self::VAR_RESOLVER_ID);
    }

    public function getPromise() : Promise
    {
        return $this->getResolver()->getPromise();
    }

    public function onCompletion(Server $server)
    {
        $result = $this->getResult();
        $resolver = $this->getResolver();
        if (is_string($result)) {
            $jsonData = json_decode($result, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (isset($jsonData[self::INDEX_ERROR_RESULT])) {
                    $error = $jsonData[self::INDEX_ERROR_RESULT];
                    $resolver->error($error);
                } else {
                    $resolver->resolve($jsonData);
                }
                return;
            }
        } else {
            $resolver->resolve($result);
        }
    }

}