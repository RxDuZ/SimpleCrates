<?php

namespace rxduz\crates\command;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use rxduz\crates\extension\Crate;
use rxduz\crates\translation\Translation;
use rxduz\crates\utils\InvMenuUtils;
use rxduz\crates\Main;

class CrateCommand extends Command
{

    public function __construct()
    {
        parent::__construct("crate", "SimpleCrates command by @zRxDuZ", null, ["cr"]);

        $this->setPermission("simplecrates.command.crate");

        $this->setPermissionMessage(TextFormat::RED . "You do not have permissions to use this command!");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Use this command in-game");

            return;
        }

        if (!$sender->hasPermission("simplecrates.command.crate")) {
            $sender->sendMessage($this->getPermissionMessage());

            return;
        }

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::RED . "Use /" . $commandLabel . " help");

            return;
        }

        switch ($args[0]) {
            case "create":
            case "make":
                if (Main::getInstance()->getCrateManager()->isConfigurator($sender->getName())) {
                    $sender->sendMessage(Translation::getInstance()->getMessage("COMMAND_CURRENTLY_SETUP"));

                    return;
                }

                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::RED . "Use /" . $commandLabel . " create <type>");

                    return;
                }

                if (Main::getInstance()->getCrateManager()->exists($args[1])) {
                    $sender->sendMessage(Translation::getInstance()->getMessage("COMMAND_CRATE_ALREADY_EXISTS"));

                    return;
                }

                Main::getInstance()->getCrateManager()->createCrate($args[1]);

                $sender->sendMessage(Translation::getInstance()->getMessage("CRATE_CREATED", ["{PREFIX}" => Main::PREFIX, "{CRATE}" => $args[1]]));
                break;
            case "remove":
            case "delete":
                if (Main::getInstance()->getCrateManager()->isConfigurator($sender->getName())) {
                    $sender->sendMessage(Translation::getInstance()->getMessage("COMMAND_CURRENTLY_SETUP"));

                    return;
                }

                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::RED . "Use /" . $commandLabel . " remove <type>");

                    return;
                }

                if (!Main::getInstance()->getCrateManager()->exists($args[1])) {
                    $sender->sendMessage(Translation::getInstance()->getMessage("COMMAND_CRATE_NOT_EXISTS"));

                    return;
                }

                Main::getInstance()->getCrateManager()->removeCrate($args[1]);

                $sender->sendMessage(Translation::getInstance()->getMessage("CRATE_REMOVED", ["{PREFIX}" => Main::PREFIX, "{CRATE}" => $args[1]]));
                break;
            case "list":
                $crates = Main::getInstance()->getCrateManager()->getCrates();

                if (empty($crates)) {
                    $sender->sendMessage(TextFormat::RED . "There are no crates registered.");

                    return;
                }

                $list = TextFormat::MINECOIN_GOLD . "Crates:" . TextFormat::EOL;

                foreach ($crates as $crate) {
                    $list .= TextFormat::GRAY . "- " . TextFormat::GREEN . $crate->getName() . TextFormat::EOL;
                }

                $sender->sendMessage($list);
                break;
            case "editor":
            case "edit":
                if (Main::getInstance()->getCrateManager()->isConfigurator($sender->getName())) {
                    $sender->sendMessage(Translation::getInstance()->getMessage("COMMAND_CURRENTLY_SETUP"));

                    return;
                }

                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::RED . "Use /" . $commandLabel . " editor <type>");

                    return;
                }

                $crate = Main::getInstance()->getCrateManager()->getCrateByName($args[1]);

                if (!$crate instanceof Crate) {
                    $sender->sendMessage(Translation::getInstance()->getMessage("COMMAND_CRATE_NOT_EXISTS"));

                    return;
                }

                InvMenuUtils::sendCrateEditorMenu($sender, $crate);
                break;
            case "help":
            default:
                $sender->sendMessage(Main::PREFIX . TextFormat::RESET . TextFormat::BLUE . "commands:");
                $sender->sendMessage(TextFormat::YELLOW . "Use /" . $commandLabel . " create <type> " . TextFormat::WHITE . "Create Crate");
                $sender->sendMessage(TextFormat::YELLOW . "Use /" . $commandLabel . " remove <type> " . TextFormat::WHITE . "Remove Crate");
                $sender->sendMessage(TextFormat::YELLOW . "Use /" . $commandLabel . " list " . TextFormat::WHITE . "View crate list");
                $sender->sendMessage(TextFormat::YELLOW . "Use /" . $commandLabel . " editor <type> " . TextFormat::WHITE . "Crate Editor");
                break;
        }
    }
}
