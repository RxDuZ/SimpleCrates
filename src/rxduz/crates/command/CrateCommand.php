<?php

namespace rxduz\crates\command;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use rxduz\crates\command\subcommand\CreateSubCommand;
use rxduz\crates\command\subcommand\DeleteSubCommand;
use rxduz\crates\command\subcommand\EditorSubCommand;
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
        $this->registerSubCommand(new CreateSubCommand('create', 'Create new crate', ['make']));
        $this->registerSubCommand(new DeleteSubCommand('delete', 'Delete crate', ['remove']));
        $this->registerSubCommand(new ListSubCommand('list', 'View crate list'));
        $this->registerSubCommand(new ReloadConfigSubCommand('reloadconfig', 'Reload all configs.'));
        $this->registerSubCommand(new EditorSubCommand('editor', 'Open crate menu editor', ['edit']));
        $this->registerSubCommand(new RenameItemSubCommand('renameitem', 'Rename an item'));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $message = Main::PREFIX . TextFormat::RESET . TextFormat::BLUE . 'commands:' . TextFormat::EOL;
        $message .= TextFormat::YELLOW . '/' . $aliasUsed . ' create <crateName> ' . TextFormat::WHITE . 'Create new crate' . TextFormat::EOL;
        $message .= TextFormat::YELLOW . '/' . $aliasUsed . ' delete <crateName> ' . TextFormat::WHITE . 'Remove a crate' . TextFormat::EOL;
        $message .= TextFormat::YELLOW . '/' . $aliasUsed . ' list ' . TextFormat::WHITE . 'View crate list' . TextFormat::EOL;
        $message .= TextFormat::YELLOW . '/' . $aliasUsed . ' reloadconfig ' . TextFormat::WHITE . 'Reload all configs' . TextFormat::EOL;
        $message .= TextFormat::YELLOW . '/' . $aliasUsed . ' editor <crateName> ' . TextFormat::WHITE . 'Crate editor menu' . TextFormat::EOL;
        $message .= TextFormat::YELLOW . '/' . $aliasUsed . ' renameitem <name|lore> <text> ' . TextFormat::WHITE . 'Rename an item ' . TextFormat::GRAY . '(Note: To skip a line in the lore use {LINE})' . TextFormat::EOL;
        $sender->sendMessage($message);
    }
}
