<?php

namespace rxduz\crates\command\subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use rxduz\crates\Main;
use rxduz\crates\translation\Translation;

class CreateSubCommand extends BaseSubCommand
{

    public const ARGUMENT_CRATE = 'crateName';

    public function prepare(): void
    {
        $this->setPermission('simplecrates.command.create');

        $this->registerArgument(0, new RawStringArgument(self::ARGUMENT_CRATE));

        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $crateName = $args[self::ARGUMENT_CRATE];

        if (Main::getInstance()->getCrateManager()->isConfigurator($sender->getName())) {
            $sender->sendMessage(Translation::getInstance()->getMessage('COMMAND_CURRENTLY_SETUP'));

            return;
        }

        if (Main::getInstance()->getCrateManager()->exists($crateName)) {
            $sender->sendMessage(Translation::getInstance()->getMessage('COMMAND_CRATE_ALREADY_EXISTS'));

            return;
        }

        Main::getInstance()->getCrateManager()->createCrate($crateName);

        $sender->sendMessage(Translation::getInstance()->getMessage('CRATE_CREATED', ['{PREFIX}' => Main::PREFIX, '{CRATE}' => $crateName]));
    }
}
