<?php

namespace rxduz\crates\command;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use rxduz\crates\command\arguments\PlayerArgument;
use rxduz\crates\extension\Crate;
use rxduz\crates\Main;
use rxduz\crates\translation\Translation;
use rxduz\crates\command\arguments\CrateArgument;

class KeyCommand extends BaseCommand
{

    public const ARGUMENT_CRATE = 'crate';

    public const ARGUMENT_AMOUNT = 'amount';

    public const ARGUMENT_TARGET = 'target';

    public function __construct(Main $plugin)
    {
        parent::__construct($plugin, 'key', 'SimpleCrates command by iRxDuZ', []);
    }

    protected function prepare(): void
    {
        $this->setPermission('simplecrates.command.key');
        $this->registerArgument(0, new CrateArgument(self::ARGUMENT_CRATE));
        $this->registerArgument(1, new IntegerArgument(self::ARGUMENT_AMOUNT, true));
        $this->registerArgument(2, new PlayerArgument(self::ARGUMENT_TARGET, true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        /** @var Crate $crate */
        $crate = $args[self::ARGUMENT_CRATE];

        $amount = 1;

        if (isset($args[self::ARGUMENT_AMOUNT])) {
            $amount = $args[self::ARGUMENT_AMOUNT];
        }

        /** @var Player|null $target */
        $target = isset($args[self::ARGUMENT_TARGET]) ? $args[self::ARGUMENT_TARGET] : $sender;

        if (!$target instanceof Player) { // In this case, use the vanilla @
            $sender->sendMessage(Translation::getInstance()->getMessage('PLAYER_NOT_ONLINE'));

            return;
        }

        $crate->giveKey($target, $amount);

        $target->sendMessage(Translation::getInstance()->getMessage('COMMAND_KEY_RECEIVED', [
            '{COUNT}' => $amount,
            '{CRATE}' => $crate->getName()
        ]));

        $sender->sendMessage(Translation::getInstance()->getMessage('COMMAND_KEY_GIVE', [
            '{PLAYER}' => $target->getName(),
            '{COUNT}' => $amount,
            '{CRATE}' => $crate->getName()
        ]));
    }
}
