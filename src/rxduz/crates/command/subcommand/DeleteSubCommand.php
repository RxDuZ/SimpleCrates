<?php

namespace rxduz\crates\command\subcommand;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use rxduz\crates\Main;
use rxduz\crates\extension\Crate;
use rxduz\crates\translation\Translation;
use rxduz\crates\command\arguments\CrateArgument;

class DeleteSubCommand extends BaseSubCommand
{

    public const ARGUMENT_CRATE = 'crateName';

    public function prepare(): void
    {
        $this->setPermission('simplecrates.command.delete');

        $this->registerArgument(0, new CrateArgument(self::ARGUMENT_CRATE));

        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        /** @var Crate $crate */
        $crate = $args[self::ARGUMENT_CRATE];

        $crateName = $crate->getName();

        if (Main::getInstance()->getCrateManager()->isConfigurator($sender->getName())) {
            $sender->sendMessage(Translation::getInstance()->getMessage('COMMAND_CURRENTLY_SETUP'));

            return;
        }

        Main::getInstance()->getCrateManager()->removeCrate($crateName);

        $sender->sendMessage(Translation::getInstance()->getMessage('CRATE_REMOVED', ['{PREFIX}' => Main::PREFIX, '{CRATE}' => $crateName]));
    }
}
