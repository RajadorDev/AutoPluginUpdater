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

namespace rajadordev\autoupdater\command\subcommand;

use pocketmine\command\CommandSender;
use rajadordev\autoupdater\api\CheckUpdateScheduler;
use rajadordev\autoupdater\Loader;
use SmartCommand\command\CommandArguments;
use SmartCommand\command\rule\defaults\CooldownRule;
use SmartCommand\command\subcommand\BaseSubCommand;

class VersionSubCommand extends BaseSubCommand
{

    protected static function getRuntimePermission(): string
    {
        return 'apu.command.version';
    }

    protected function prepare()
    {
        $this->registerRule(new CooldownRule(CooldownRule::secondsToMs(1)));
    }

    protected function onRun(CommandSender $sender, string $commandLabel, string $subcommandLabel, CommandArguments $args)
    {
        $updater = CheckUpdateScheduler::getInstance()->getPluginUpdater(Loader::getInstance());
        $sender->sendMessage($updater->infoText());
    }
}