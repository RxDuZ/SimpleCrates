<?php

namespace rxduz\crates\utils;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use rxduz\crates\CrateManager;
use rxduz\crates\extension\Crate;
use rxduz\crates\Main;
use rxduz\crates\translation\Translation;

class InvMenuUtils
{

    /**
     * @param Player $player
     * @param Crate $crate
     */
    public static function sendCrateEditorMenu(Player $player, Crate $crate): void
    {
        $menu = InvMenu::create(InvMenu::TYPE_CHEST);

        $menu->setName(TextFormat::BOLD . TextFormat::GOLD . "Crate Editor");

        $menu->setListener(function (InvMenuTransaction $transaction) use ($crate): InvMenuTransactionResult {
            $player = $transaction->getPlayer();

            $item = $transaction->getItemClicked();

            switch ($item->getStateId()) {
                case VanillaBlocks::CHEST()->asItem()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_CURRENTLY_SETUP"));

                        return $transaction->discard();
                    }

                    if (Main::getInstance()->getPositionManager()->exists($crate->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_CRATE_POSITION_ALREADY_EXISTS"));

                        return $transaction->discard();
                    }

                    Main::getInstance()->getCrateManager()->setConfigurator(
                        new Configurator(
                            $player->getName(),
                            $crate,
                            CrateManager::CONFIGURATOR_SET_CRATE
                        )
                    );

                    $player->removeCurrentWindow();

                    $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_SETUP_SET_BLOCK", ["{PREFIX}" => Main::PREFIX]));
                    break;
                case VanillaBlocks::BEDROCK()->asItem()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_CURRENTLY_SETUP"));

                        return $transaction->discard();
                    }

                    if (!Main::getInstance()->getPositionManager()->exists($crate->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_CRATE_POSITION_NOT_EXISTS"));

                        return $transaction->discard();
                    }

                    $crate->close();

                    Main::getInstance()->getPositionManager()->removePosition($crate->getName());

                    $player->removeCurrentWindow();

                    $player->sendMessage(Translation::getInstance()->getMessage("CRATE_REMOVE_BLOCK", [
                        "{PREFIX}" => Main::PREFIX,
                        "{CRATE}" => $crate->getName()
                    ]));
                    break;
                case VanillaItems::EMERALD()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_CURRENTLY_SETUP"));

                        return $transaction->discard();
                    }

                    //$player->getInventory()->clearAll();

                    //$player->getCursorInventory()->clearAll();

                    self::sendEditCrateItemChance($player, $crate);
                    break;
                case VanillaItems::PAPER()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_CURRENTLY_SETUP"));

                        return $transaction->discard();
                    }

                    $player->removeCurrentWindow();

                    $player->getInventory()->clearAll();

                    $player->getCursorInventory()->clearAll();

                    self::sendEditCrateItemCommands($player, $crate);
                    break;
                case VanillaItems::DIAMOND()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_CURRENTLY_SETUP"));

                        return $transaction->discard();
                    }

                    $player->removeCurrentWindow();

                    self::sendEditCrateInventory($player, $crate);
                    break;
                case VanillaItems::NAME_TAG()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_CURRENTLY_SETUP"));

                        return $transaction->discard();
                    }

                    Main::getInstance()->getCrateManager()->setConfigurator(
                        new Configurator(
                            $player->getName(),
                            $crate,
                            CrateManager::CONFIGURATOR_CRATE_HOLOGRAMS
                        )
                    );

                    $player->removeCurrentWindow();

                    $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_SETUP_CRATE_HOLOGRAM", ["{PREFIX}" => Main::PREFIX]));
                    break;
                case VanillaItems::BOOK()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_CURRENTLY_SETUP"));

                        return $transaction->discard();
                    }

                    Main::getInstance()->getCrateManager()->setConfigurator(
                        new Configurator(
                            $player->getName(),
                            $crate,
                            CrateManager::CONFIGURATOR_CRATE_COMMANDS
                        )
                    );

                    $player->removeCurrentWindow();

                    $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_SETUP_CRATE_COMMAND", ["{PREFIX}" => Main::PREFIX]));
                    break;
                case VanillaItems::AMETHYST_SHARD()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_CURRENTLY_SETUP"));

                        return $transaction->discard();
                    }

                    Main::getInstance()->getCrateManager()->setConfigurator(
                        new Configurator(
                            $player->getName(),
                            $crate,
                            CrateManager::CONFIGURATOR_CRATE_PARTICLE
                        )
                    );

                    $player->removeCurrentWindow();

                    $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_SETUP_CRATE_PARTICLE", ["{PREFIX}" => Main::PREFIX]));
                    break;
                case VanillaItems::BLAZE_POWDER()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_CURRENTLY_SETUP"));

                        return $transaction->discard();
                    }

                    Main::getInstance()->getCrateManager()->setConfigurator(
                        new Configurator(
                            $player->getName(),
                            $crate,
                            CrateManager::CONFIGURATOR_CRATE_PARTICLE_COLOR
                        )
                    );

                    $player->removeCurrentWindow();

                    $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_SETUP_CRATE_PARTICLE_COLOR", ["{PREFIX}" => Main::PREFIX]));
                    break;
            }

            return $transaction->discard();
        });

        $menu->getInventory()->setItem(0, VanillaBlocks::CHEST()->asItem()->setCustomName(TextFormat::BOLD . TextFormat::AQUA . "Set Crate Block"));

        $menu->getInventory()->setItem(1, VanillaBlocks::BEDROCK()->asItem()->setCustomName(TextFormat::BOLD . TextFormat::RED . "Remove Crate Block"));

        $menu->getInventory()->setItem(2, VanillaItems::EMERALD()->setCustomName(TextFormat::BOLD . TextFormat::GREEN . "Edit Items Chance"));

        $menu->getInventory()->setItem(3, VanillaItems::PAPER()->setCustomName(TextFormat::BOLD . TextFormat::YELLOW . "Edit Items Commands"));

        $menu->getInventory()->setItem(4, VanillaItems::DIAMOND()->setCustomName(TextFormat::BOLD . TextFormat::AQUA . "Edit Crate Inventory"));

        $menu->getInventory()->setItem(5, VanillaItems::NAME_TAG()->setCustomName(TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Edit Crate Hologram"));

        $menu->getInventory()->setItem(6, VanillaItems::BOOK()->setCustomName(TextFormat::BOLD . TextFormat::GOLD . "Edit Crate Commands"));

        $menu->getInventory()->setItem(7, VanillaItems::AMETHYST_SHARD()->setCustomName(TextFormat::BOLD . TextFormat::MINECOIN_GOLD . "Edit Particle Id"));

        $menu->getInventory()->setItem(8, VanillaItems::BLAZE_POWDER()->setCustomName(TextFormat::BOLD . TextFormat::DARK_PURPLE . "Edit Particle " . TextFormat::RED . "R" . TextFormat::GREEN . "G" . TextFormat::BLUE . "B"));

        $menu->send($player);
    }

    /**
     * @param Player $player
     * @param Crate $crate
     */
    public static function sendEditCrateInventory(Player $player, Crate $crate): void
    {
        $menu = InvMenu::create(InvMenu::TYPE_CHEST);

        $menu->setName(TextFormat::BOLD . TextFormat::GOLD . $crate->getName() . " Inventory Editor");

        $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($crate): void {
            $crate->setItems($inventory->getContents());

            $player->sendMessage(TextFormat::GREEN . "The items were saved successfully");
        });

        $menu->getInventory()->setContents($crate->getItems());

        $menu->send($player);
    }

    /**
     * @param Player $player
     * @param Crate $crate
     */
    public static function sendEditCrateItemChance(Player $player, Crate $crate): void
    {
        $menu = InvMenu::create(InvMenu::TYPE_CHEST);

        $menu->setName(TextFormat::BOLD . TextFormat::GOLD . $crate->getName() . " Chance Editor");

        $menu->setListener(function (InvMenuTransaction $transaction) use ($crate): InvMenuTransactionResult {
            $player = $transaction->getPlayer();

            $item = $transaction->getItemClicked();

            if ($crate->isValidItem($item)) {
                $slot = $transaction->getAction()->getSlot();

                Main::getInstance()->getCrateManager()->setConfigurator(new Configurator(
                    $player->getName(),
                    $crate,
                    CrateManager::CONFIGURATOR_ITEM_CHANCE,
                    $slot,
                    $item
                ));

                $player->removeCurrentWindow();

                $player->sendMessage(
                    TextFormat::YELLOW . "Current Chance: " . TextFormat::WHITE . strval($crate->getChanceToItem($item))
                );

                $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_SETUP_SET_CHANCE", ["{PREFIX}" => Main::PREFIX]));
            }

            return $transaction->discard();
        });

        $menu->getInventory()->setContents($crate->getItems());

        $menu->send($player);
    }

    /**
     * @param Player $player
     * @param Crate $crate
     */
    public static function sendEditCrateItemCommands(Player $player, Crate $crate): void
    {
        $menu = InvMenu::create(InvMenu::TYPE_CHEST);

        $menu->setName(TextFormat::BOLD . TextFormat::GOLD . $crate->getName() . " Command Editor");

        $menu->setListener(function (InvMenuTransaction $transaction) use ($crate): InvMenuTransactionResult {
            $player = $transaction->getPlayer();

            $item = $transaction->getItemClicked();

            if ($crate->isValidItem($item)) {
                $slot = $transaction->getAction()->getSlot();

                Main::getInstance()->getCrateManager()->setConfigurator(new Configurator(
                    $player->getName(),
                    $crate,
                    CrateManager::CONFIGURATOR_ITEM_COMMAND,
                    $slot,
                    $item
                ));

                $player->removeCurrentWindow();

                $commands = $crate->getCommandsToItem($item);

                $count = count($crate->getCommandsToItem($item));

                $player->sendMessage(
                    TextFormat::YELLOW . "Current Commands (" . $count . "): " . TextFormat::WHITE . (empty($commands) ? "Empty" : implode(",", $commands))
                );

                $player->sendMessage(Translation::getInstance()->getMessage("COMMAND_SETUP_SET_COMMAND", ["{PREFIX}" => Main::PREFIX]));
            }

            return $transaction->discard();
        });

        $menu->getInventory()->setContents($crate->getItems());

        $menu->send($player);
    }
}
