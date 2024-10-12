<?php

namespace rxduz\crates\command;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use rxduz\crates\command\subcommand\CreateSubCommand;
use rxduz\crates\command\subcommand\DeleteSubCommand;
use rxduz\crates\command\subcommand\EditorSubCommand;
use rxduz\crates\command\subcommand\ListSubCommand;
use rxduz\crates\Main;

class CrateCommand extends BaseCommand
{

    public function __construct(private Main $plugin)
    {
        parent::__construct($plugin, 'crate', 'SimpleCrates command by @zRxDuZ', ['cr']);
    }

    public function prepare(): void
    {
        $this->setPermission('simplecrates.command.create');

        $this->registerSubCommand(new CreateSubCommand('create', 'Create new crate', ['make']));

        $this->registerSubCommand(new DeleteSubCommand('delete', 'Delete crate', ['remove']));

        $this->registerSubCommand(new ListSubCommand('list', 'View crate list'));

        $this->registerSubCommand(new EditorSubCommand('editor', 'Open crate menu editor', ['edit']));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $sender->sendMessage(Main::PREFIX . TextFormat::RESET . TextFormat::BLUE . 'commands:');
        $sender->sendMessage(TextFormat::YELLOW . 'Use /' . $aliasUsed . ' create <type> ' . TextFormat::WHITE . 'Create Crate');
        $sender->sendMessage(TextFormat::YELLOW . 'Use /' . $aliasUsed . ' remove <type> ' . TextFormat::WHITE . 'Remove Crate');
        $sender->sendMessage(TextFormat::YELLOW . 'Use /' . $aliasUsed . ' list ' . TextFormat::WHITE . 'View crate list');
        $sender->sendMessage(TextFormat::YELLOW . 'Use /' . $aliasUsed . ' editor <type> ' . TextFormat::WHITE . 'Crate Editor');
    }
}
