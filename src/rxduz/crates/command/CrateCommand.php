<?php

namespace rxduz\crates\command;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use rxduz\crates\command\subcommand\CreateSubCommand;
use rxduz\crates\command\subcommand\DeleteSubCommand;
use rxduz\crates\command\subcommand\EditorSubCommand;
use rxduz\crates\command\subcommand\HelpSubCommand;
use rxduz\crates\command\subcommand\ListSubCommand;
use rxduz\crates\command\subcommand\ReloadConfigSubCommand;
use rxduz\crates\command\subcommand\RenameItemSubCommand;
use rxduz\crates\Main;

class CrateCommand extends BaseCommand
{

    public function __construct(Main $plugin)
    {
        parent::__construct($plugin, 'crate', 'SimpleCrates command by iRxDuZ', ['cr']);
    }

    public function prepare(): void
    {
        $this->setPermission('simplecrates.command');
        $this->registerSubCommand(new HelpSubCommand($this, 'help', 'Display command list', ['?', 'h']));
        $this->registerSubCommand(new CreateSubCommand($this->plugin, 'create', 'Create new crate', ['make']));
        $this->registerSubCommand(new DeleteSubCommand($this->plugin, 'delete', 'Remove a crate', ['remove']));
        $this->registerSubCommand(new ListSubCommand($this->plugin, 'list', 'View crate list'));
        $this->registerSubCommand(new ReloadConfigSubCommand($this->plugin, 'reloadconfig', 'Reload all configs.'));
        $this->registerSubCommand(new EditorSubCommand($this->plugin, 'editor', 'Open crate menu editor', ['edit']));
        $this->registerSubCommand(new RenameItemSubCommand($this->plugin, 'renameitem', 'Rename an item'), ['rename']);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $sender->sendMessage(TextFormat::GREEN . '[SimpleCrates] made by iRxDuZ');
    }
}
