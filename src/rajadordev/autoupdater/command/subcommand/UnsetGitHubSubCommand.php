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
use rajadordev\autoupdater\api\plugin\defaults\github\GitHubPluginUpdaterAPI;
use rajadordev\autoupdater\Loader;
use SmartCommand\command\CommandArguments;
use SmartCommand\command\subcommand\BaseSubCommand;

class UnsetGitHubSubCommand extends BaseSubCommand
{

    protected static function getRuntimePermission(): string
    {
        return 'apu.command.setgittoken';
    }

    protected function prepare()
    {}

    protected function onRun(CommandSender $sender, string $commandLabel, string $subcommandLabel, CommandArguments $args)
    {
        GitHubPluginUpdaterAPI::deleteGitHubToken();
        $sender->sendMessage(Loader::PREFIX . "§bGitHub token §cwas deleted successfully");
    }

}