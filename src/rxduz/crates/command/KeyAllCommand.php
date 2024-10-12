<?php

namespace rxduz\crates\command;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use rxduz\crates\extension\Crate;
use rxduz\crates\Main;
use rxduz\crates\translation\Translation;
use rxduz\crates\command\arguments\CrateArgument;

class KeyAllCommand extends BaseCommand
{

    public const ARGUMENT_CRATE = 'crate';

    public const ARGUMENT_AMOUNT = 'amount';

    public function __construct(private Main $plugin)
    {
        parent::__construct($plugin, 'keyall', 'SimpleCrates command by @zRxDuZ', []);
    }

    public function prepare(): void
    {
        $this->setPermission('simplecrates.command.keyall');

        $this->registerArgument(0, new CrateArgument(self::ARGUMENT_CRATE));

        $this->registerArgument(1, new IntegerArgument(self::ARGUMENT_AMOUNT, true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        /** @var Crate $crate */
        $crate = $args[self::ARGUMENT_CRATE];

        $amount = 1;

        if (isset($args[self::ARGUMENT_AMOUNT])) {
            $amount = $args[self::ARGUMENT_AMOUNT];
        }

        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $crate->giveKey($player, $amount);

            $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_KEY_RECEIVED', [
                '{COUNT}' => $amount,
                '{CRATE}' => $crate->getName()
            ]));
        }

        $sender->sendMessage(Translation::getInstance()->getMessage('COMMAND_KEYALL_GIVE', [
            '{COUNT}' => $amount,
            '{CRATE}' => $crate->getName()
        ]));
    }
}
