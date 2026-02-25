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

namespace rajadordev\autoupdater\command;

use pocketmine\command\CommandSender;
use rajadordev\autoupdater\command\subcommand\SetGitTokenSubCommand;
use rajadordev\autoupdater\command\subcommand\VersionSubCommand;
use rajadordev\autoupdater\Loader;
use SmartCommand\command\CommandArguments;
use SmartCommand\command\SmartCommand;

class AutoPluginUpdaterCommand extends SmartCommand
{

    protected static function getRuntimePermission(): string
    {
        return 'apu.command.main';
    }

    protected function prepare()
    {
        $this->setPrefix(Loader::PREFIX);
        $this->registerSubCommands([
            new VersionSubCommand($this, 'version', 'Shows APU version', ['ver']),
            new SetGitTokenSubCommand($this, 'setghtoken', 'Set the GitHub token')
        ]);
    }

    protected function onRun(CommandSender $sender, string $label, CommandArguments $args)
    {
        $this->sendUsage($sender, $label);
    }
}