<?php

namespace rxduz\crates\command\subcommand;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use rxduz\crates\Main;

class ListSubCommand extends BaseSubCommand
{

    protected function prepare(): void
    {
        $this->setPermission('simplecrates.command.list');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $crates = Main::getInstance()->getCrateManager()->getCrates();

        if (empty($crates)) {
            $sender->sendMessage(TextFormat::RED . 'There are no crates registered.');

            return;
        }

        $list = TextFormat::MINECOIN_GOLD . 'Crates:' . TextFormat::EOL;

        foreach ($crates as $crate) {
            $list .= TextFormat::GRAY . '- ' . TextFormat::GREEN . $crate->getName() . TextFormat::EOL;
        }

        $sender->sendMessage($list);
    }
}
