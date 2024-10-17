<?php

namespace rxduz\crates\utils;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\item\Item;
use pocketmine\block\VanillaBlocks;
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

        $menu->setName(TextFormat::BOLD . TextFormat::GOLD . 'Crate ' . $crate->getName() . ' Editor');

        $menu->setListener(function (InvMenuTransaction $transaction) use ($crate): InvMenuTransactionResult {
            $player = $transaction->getPlayer();

            $item = $transaction->getItemClicked();

            switch ($item->getStateId()) {
                case VanillaBlocks::CHEST()->asItem()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_CURRENTLY_SETUP'));

                        return $transaction->discard();
                    }

                    if (Main::getInstance()->getPositionManager()->exists($crate->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_CRATE_POSITION_ALREADY_EXISTS'));

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

                    $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_SETUP_SET_BLOCK', ['{PREFIX}' => Main::PREFIX]));
                    break;
                case VanillaBlocks::BEDROCK()->asItem()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_CURRENTLY_SETUP'));

                        return $transaction->discard();
                    }

                    if (!Main::getInstance()->getPositionManager()->exists($crate->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_CRATE_POSITION_NOT_EXISTS'));

                        return $transaction->discard();
                    }

                    $crate->close();

                    Main::getInstance()->getPositionManager()->removePosition($crate->getName());

                    $player->removeCurrentWindow();

                    $player->sendMessage(Translation::getInstance()->getMessage('CRATE_REMOVE_BLOCK', [
                        '{PREFIX}' => Main::PREFIX,
                        '{CRATE}' => $crate->getName()
                    ]));
                    break;
                case VanillaItems::EMERALD()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_CURRENTLY_SETUP'));

                        return $transaction->discard();
                    }

                    self::sendInventoryChanceItemMenu($player, $crate);
                    break;
                case VanillaItems::PAPER()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_CURRENTLY_SETUP'));

                        return $transaction->discard();
                    }

                    self::sendInventoryCommandItemMenu($player, $crate);
                    break;
                case VanillaItems::DIAMOND()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_CURRENTLY_SETUP'));

                        return $transaction->discard();
                    }

                    self::sendInventoryEditorMenu($player, $crate);
                    break;
                case VanillaItems::NAME_TAG()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_CURRENTLY_SETUP'));

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

                    $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_SETUP_CRATE_HOLOGRAM', ['{PREFIX}' => Main::PREFIX]));
                    break;
                case VanillaItems::BOOK()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_CURRENTLY_SETUP'));

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

                    $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_SETUP_CRATE_COMMAND', ['{PREFIX}' => Main::PREFIX]));
                    break;
                case VanillaItems::AMETHYST_SHARD()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_CURRENTLY_SETUP'));

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

                    $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_SETUP_CRATE_PARTICLE', ['{PREFIX}' => Main::PREFIX]));
                    break;
                case VanillaItems::BLAZE_POWDER()->getStateId():
                    if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
                        $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_CURRENTLY_SETUP'));

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

                    $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_SETUP_CRATE_PARTICLE_COLOR', ['{PREFIX}' => Main::PREFIX]));
                    break;
            }

            return $transaction->discard();
        });

        $menu->getInventory()->setItem(0, VanillaBlocks::CHEST()->asItem()->setCustomName(TextFormat::BOLD . TextFormat::AQUA . 'Set Crate Block'));

        $menu->getInventory()->setItem(1, VanillaBlocks::BEDROCK()->asItem()->setCustomName(TextFormat::BOLD . TextFormat::RED . 'Remove Crate Block'));

        $menu->getInventory()->setItem(2, VanillaItems::EMERALD()->setCustomName(TextFormat::BOLD . TextFormat::GREEN . 'Edit Items Chance'));

        $menu->getInventory()->setItem(3, VanillaItems::PAPER()->setCustomName(TextFormat::BOLD . TextFormat::YELLOW . 'Edit Items Commands'));

        $menu->getInventory()->setItem(4, VanillaItems::DIAMOND()->setCustomName(TextFormat::BOLD . TextFormat::AQUA . 'Edit Crate Inventory'));

        $menu->getInventory()->setItem(5, VanillaItems::NAME_TAG()->setCustomName(TextFormat::BOLD . TextFormat::LIGHT_PURPLE . 'Edit Crate Hologram'));

        $menu->getInventory()->setItem(6, VanillaItems::BOOK()->setCustomName(TextFormat::BOLD . TextFormat::GOLD . 'Edit Crate Commands'));

        $menu->getInventory()->setItem(7, VanillaItems::AMETHYST_SHARD()->setCustomName(TextFormat::BOLD . TextFormat::MINECOIN_GOLD . 'Edit Particle Id'));

        $menu->getInventory()->setItem(8, VanillaItems::BLAZE_POWDER()->setCustomName(TextFormat::BOLD . TextFormat::DARK_PURPLE . 'Edit Particle ' . TextFormat::RED . 'R' . TextFormat::GREEN . 'G' . TextFormat::BLUE . 'B'));

        $menu->send($player);
    }

    /**
     * @param Player $player
     * @param Crate $crate
     */
    public static function sendInventoryEditorMenu(Player $player, Crate $crate): void
    {
        $menu = InvMenu::create(InvMenu::TYPE_CHEST);

        $menu->setName(TextFormat::BOLD . TextFormat::GOLD . $crate->getName() . ' Inventory Editor');

        $menu->setListener(function (InvMenuTransaction $transaction) use ($crate): InvMenuTransactionResult {
            $player = $transaction->getPlayer();

            $item = $transaction->getItemClicked();

            $itemClickedWith = $transaction->getItemClickedWith();

            if ($item->getTypeId() === VanillaItems::APPLE()->getTypeId()) {
                $drop = CrateManager::DEFAULT_ITEM_DATA;

                $drop['item'] = ($itemClickedWith->isNull() ? VanillaItems::APPLE() : $itemClickedWith);

                $contents = $crate->getDrops();

                $slot = Utils::firstFreeKey($contents);

                $contents[$slot] = $drop;

                $crate->setDrops($contents);

                $player->sendMessage(Translation::getInstance()->getMessage('SETUP_SUCCESS_ADD_REWARD'));

                Utils::playSound($player, 'random.orb');
            } else if ($item->getTypeId() === VanillaItems::EMERALD()->getTypeId()) {
                self::sendInventoryChangeItemMenu($player, $crate);
            } else if ($item->getTypeId() === VanillaItems::COAL()->getTypeId()) {
                self::sendInventoryRemoveItemMenu($player, $crate);
            }
            return $transaction->discard();
        });

        $menu->getInventory()->setItem(11, VanillaItems::APPLE()->setCustomName(TextFormat::GREEN . 'Add Item'));

        $menu->getInventory()->setItem(13, VanillaItems::EMERALD()->setCustomName(TextFormat::GOLD . 'Change Item'));

        $menu->getInventory()->setItem(15, VanillaItems::COAL()->setCustomName(TextFormat::RED . 'Remove Item'));

        $menu->send($player);
    }

    /**
     * @param Player $player
     * @param Crate $crate
     */
    public static function sendInventoryChangeItemMenu(Player $player, Crate $crate): void
    {
        $menu = InvMenu::create(count($crate->getDrops()) >= 27 ? InvMenu::TYPE_DOUBLE_CHEST : InvMenu::TYPE_CHEST);

        $menu->setName(TextFormat::BOLD . TextFormat::GOLD . $crate->getName() . ' Change Inventory Item');

        $menu->setListener(function (InvMenuTransaction $transaction) use ($crate): InvMenuTransactionResult {
            $player = $transaction->getPlayer();

            $item = $transaction->getItemClicked();

            if ($item->getNamedTag()->getTag('slot') !== null) {
                $slot = $item->getNamedTag()->getInt('slot');

                Main::getInstance()->getCrateManager()->setConfigurator(new Configurator(
                    $player->getName(),
                    $crate,
                    CrateManager::CONFIGURATOR_ITEM_CHANGE,
                    $slot
                ));

                $player->removeCurrentWindow();

                $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_SETUP_SET_CHANGE', ['{PREFIX}' => Main::PREFIX]));
            }

            return $transaction->discard();
        });

        foreach ($crate->getDrops() as $slot => $drop) {
            /** @var Item|null */
            $item = $drop['item'] ?? null;

            if ($item === null) {
                $item = VanillaItems::APPLE();
            }

            $item->getNamedTag()->setInt('slot', $slot);

            $menu->getInventory()->setItem($slot, $item);
        }

        $menu->send($player);
    }

    /**
     * @param Player $player
     * @param Crate $crate
     */
    public static function sendInventoryRemoveItemMenu(Player $player, Crate $crate): void
    {
        $menu = InvMenu::create(count($crate->getDrops()) >= 27 ? InvMenu::TYPE_DOUBLE_CHEST : InvMenu::TYPE_CHEST);

        $menu->setName(TextFormat::BOLD . TextFormat::GOLD . $crate->getName() . ' Remove Inventory Item');

        $menu->setListener(function (InvMenuTransaction $transaction) use ($menu, $crate): InvMenuTransactionResult {
            $player = $transaction->getPlayer();

            $item = $transaction->getItemClicked();

            if ($item->getNamedTag()->getTag('slot') !== null) {
                $slot = $item->getNamedTag()->getInt('slot');

                $drops = $crate->getDrops();

                if (array_key_exists($slot, $drops)) {
                    unset($drops[$slot]);
                }

                $crate->setDrops($drops);

                $player->sendMessage(Translation::getInstance()->getMessage('SETUP_SUCCESS_DELETED_REWARD', ['{SLOT}' => strval($slot)]));

                Utils::playSound($player, 'note.bass');

                self::setInventoryRemoveItems($menu, $crate);
            }
            return $transaction->discard();
        });

        self::setInventoryRemoveItems($menu, $crate);

        $menu->send($player);
    }

    /**
     * @param InvMenu $menu
     * @param Crate $crate
     */
    public static function setInventoryRemoveItems(InvMenu $menu, Crate $crate): void
    {
        $menu->getInventory()->clearAll();

        foreach ($crate->getDrops() as $slot => $drop) {
            /** @var Item|null */
            $item = $drop['item'] ?? null;

            if ($item === null) {
                $item = VanillaItems::APPLE();
            }

            $item->getNamedTag()->setInt('slot', $slot);

            $menu->getInventory()->setItem($slot, $item);
        }
    }

    /**
     * @param Player $player
     * @param Crate $crate
     */
    public static function sendInventoryChanceItemMenu(Player $player, Crate $crate): void
    {
        $menu = InvMenu::create(count($crate->getDrops()) > 27 ? InvMenu::TYPE_DOUBLE_CHEST : InvMenu::TYPE_CHEST);

        $menu->setName(TextFormat::BOLD . TextFormat::GOLD . $crate->getName() . ' Set Chance Inventory Item');

        $menu->setListener(function (InvMenuTransaction $transaction) use ($crate): InvMenuTransactionResult {
            $player = $transaction->getPlayer();

            $item = $transaction->getItemClicked();

            if ($item->getNamedTag()->getTag('slot') !== null) {
                $slot = $item->getNamedTag()->getInt('slot');

                Main::getInstance()->getCrateManager()->setConfigurator(new Configurator(
                    $player->getName(),
                    $crate,
                    CrateManager::CONFIGURATOR_ITEM_CHANCE,
                    $slot
                ));

                $player->removeCurrentWindow();

                $player->sendMessage(
                    TextFormat::YELLOW . 'Current Chance: ' . TextFormat::WHITE . strval($crate->getDrops()[$slot]['chance'] ?? 10)
                );

                $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_SETUP_SET_CHANCE', ['{PREFIX}' => Main::PREFIX]));
            }

            return $transaction->discard();
        });

        foreach ($crate->getDrops() as $slot => $drop) {
            /** @var Item|null */
            $item = $drop['item'] ?? null;

            if ($item === null) {
                $item = VanillaItems::APPLE();
            }

            $item->getNamedTag()->setInt('slot', $slot);

            $menu->getInventory()->setItem($slot, $item);
        }

        $menu->send($player);
    }

    /**
     * @param Player $player
     * @param Crate $crate
     */
    public static function sendInventoryCommandItemMenu(Player $player, Crate $crate): void
    {
        $menu = InvMenu::create(count($crate->getDrops()) > 27 ? InvMenu::TYPE_DOUBLE_CHEST : InvMenu::TYPE_CHEST);

        $menu->setName(TextFormat::BOLD . TextFormat::GOLD . $crate->getName() . ' Set Command Inventory Item');

        $menu->setListener(function (InvMenuTransaction $transaction) use ($crate): InvMenuTransactionResult {
            $player = $transaction->getPlayer();

            $item = $transaction->getItemClicked();

            if ($item->getNamedTag()->getTag('slot') !== null) {
                $slot = $item->getNamedTag()->getInt('slot');

                Main::getInstance()->getCrateManager()->setConfigurator(new Configurator(
                    $player->getName(),
                    $crate,
                    CrateManager::CONFIGURATOR_ITEM_COMMAND,
                    $slot
                ));

                $player->removeCurrentWindow();

                $commands = $crate->getDrops()[$slot]['commands'] ?? [];

                $count = count($commands);

                $player->sendMessage(
                    TextFormat::YELLOW . 'Current Commands (' . $count . '): ' . TextFormat::WHITE . (empty($commands) ? 'Empty' : implode(',', $commands))
                );

                $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_SETUP_SET_COMMAND', ['{PREFIX}' => Main::PREFIX]));
            }

            return $transaction->discard();
        });

        foreach ($crate->getDrops() as $slot => $drop) {
            /** @var Item|null */
            $item = $drop['item'] ?? null;

            if ($item === null) {
                $item = VanillaItems::APPLE();
            }

            $item->getNamedTag()->setInt('slot', $slot);

            $menu->getInventory()->setItem($slot, $item);
        }

        $menu->send($player);
    }
}
