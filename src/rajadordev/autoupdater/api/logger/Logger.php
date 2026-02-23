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

namespace rajadordev\autoupdater\api\logger;

use pocketmine\plugin\PluginLogger;

class Logger 
{

    /** @var string */
    protected $filePath;

    /** @var PluginLogger */
    protected $pluginLogger;

    public function __construct(
        string $filePath,
        PluginLogger $pluginLogger
    )
    {
        $this->filePath = $filePath;
        $this->pluginLogger = $pluginLogger;
        if (!file_exists($filePath)) {
            file_put_contents($filePath, '');
        }
        $this->info("Log file created in $filePath");
    }

    public function currentDate() : string 
    {
        return date('d/m/Y [H:i:s]');
    }

    protected function log(string $prefix, string $text)
    {
        $prefix = $this->currentDate() . ' [' . $prefix . '] : ';
        $totalText = '';
        foreach (explode("\n", $text) as $textLine) {
            $totalText .= $textLine . "\n";
        }
        $this->write($prefix . $totalText);
    }

    public function info(string $info, bool $sendToConsole = true)
    {
        $this->log('INFO', $info);
        if ($sendToConsole) {
            $this->pluginLogger->info($info);
        }
    }

    public function debug(string $debug, bool $sendToConsole = true)
    {
        $this->log('DEBUG', $debug);
        if ($sendToConsole) {
            $this->pluginLogger->debug($debug);
        }
    }

    public function alert(string $alert, bool $sendToConsole = true)
    {
        $this->log('ALERT', $alert);
        if ($sendToConsole) {
            $this->pluginLogger->alert($alert);
        }
    }

    public function error(string $error, bool $sendToConsole = true)
    {
        $this->log('ERROR', $error);
        if ($sendToConsole) {
            $this->pluginLogger->error($error);
        }
    }

    public function notice(string $notice, bool $sendToConsole = true)
    {
        $this->log('NOTICE', $notice);
        if ($sendToConsole) {
            $this->pluginLogger->notice($notice);
        }
    }

    public function warning(string $warn, bool $sendToConsole = true)
    {
        $this->log('WARNING', $warn);
        if ($sendToConsole) {
            $this->pluginLogger->warning($warn);
        }
    }

    protected function write(string $text)
    {
        $file = fopen($this->filePath, 'a');
        fwrite($file, $text);
        fclose($file);
    }

}