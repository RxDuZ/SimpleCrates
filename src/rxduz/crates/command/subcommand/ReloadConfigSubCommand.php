<?php

namespace rxduz\crates\command\subcommand;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use rxduz\crates\Main;
use rxduz\crates\translation\Translation;

class ReloadConfigSubCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission('simplecrates.command.reloadconfig');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        Translation::getInstance()->load(Main::getInstance());

        Main::getInstance()->getConfig()->reload();

        $sender->sendMessage(Translation::getInstance()->getMessage('RELOAD_ALL_CONFIGS'));
    }
}
