<?php

namespace rxduz\crates\command;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use rxduz\crates\extension\Crate;
use rxduz\crates\Main;
use rxduz\crates\translation\Translation;

class KeyCommand extends Command {

    public function __construct(){
        parent::__construct("key", "SimpleCrates command by @zRxDuZ", null, []);

        $this->setPermission("simplecrates.command.key");

        $this->setPermissionMessage(TextFormat::RED . "You do not have permissions to use this command!");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
        if(!$sender->hasPermission("simplecrates.command.key")){
            $sender->sendMessage($this->getPermissionMessage());

            return;
        }
        
        if(!isset($args[0])){
            $sender->sendMessage(TextFormat::RED . "Use /" . $commandLabel . " <type> <amount> <optional: player>");

            return;
        }

        $crate = Main::getInstance()->getCrateManager()->getCrateByName($args[0]);

        if(!$crate instanceof Crate){
            $sender->sendMessage(Translation::getInstance()->getMessage("COMMAND_CRATE_NOT_EXISTS"));

            return;
        }

        $amount = 1;

        if(isset($args[1])){
            $amount = $args[1];
        }

        if(!is_numeric($amount)){
            $sender->sendMessage(Translation::getInstance()->getMessage("COMMAND_USE_NUMERIC_VALUE"));

            return;
        }

        $target = isset($args[2]) ? Server::getInstance()->getPlayerByPrefix($args[2]) : $sender;

        if(!$target instanceof Player){
            $sender->sendMessage(Translation::getInstance()->getMessage("PLAYER_NOT_ONLINE"));

            return;
        }

        $crate->giveKey($target, intval($amount));

        $target->sendMessage(Translation::getInstance()->getMessage("COMMAND_KEY_RECEIVED", [
            "{COUNT}" => $amount,
            "{CRATE}" => $crate->getName()
        ]));

        $sender->sendMessage(Translation::getInstance()->getMessage("COMMAND_KEY_GIVE", [
            "{PLAYER}" => $target->getName(),
            "{COUNT}" => $amount,
            "{CRATE}" => $crate->getName()
        ]));
    }

}
?>