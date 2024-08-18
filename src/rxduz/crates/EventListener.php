<?php

namespace rxduz\crates;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\ItemSpawnEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\BlockBreakParticle;
use rxduz\crates\extension\Crate;
use rxduz\crates\libs\texter\SendType;
use rxduz\crates\task\SendTextsTask;
use rxduz\crates\translation\Translation;

class EventListener implements Listener
{

    /**
     * @param ItemSpawnEvent $ev
     */
    public function onEntitySpawn(ItemSpawnEvent $ev): void
    {
        $entity = $ev->getEntity();

        if ($entity->getItem()->getNamedTag()->getTag("CrateItem") !== null) {
            $entity->setNameTag(Translation::getInstance()->getMessage("CRATE_ITEM_NAME_INVENTORY", ["{NAME}" => $entity->getItem()->getName(), "{COUNT}" => $entity->getItem()->getCount()]));
            $entity->setNameTagAlwaysVisible();
        }
    }

    /**
     * @param PlayerJoinEvent $ev
     */
    public function onPlayerJoin(PlayerJoinEvent $ev): void
    {
        $player = $ev->getPlayer();

        $world = $player->getWorld();

        $sendTask = new SendTextsTask($player, $world, SendType::ADD);

        Main::getInstance()->getScheduler()->scheduleDelayedRepeatingTask($sendTask, SendTextsTask::DELAY_TICKS, SendTextsTask::TICKING_PERIOD);
    }

    /**
     * @param EntityTeleportEvent $ev
     */
    public function onEntityTeleport(EntityTeleportEvent $ev)
    {
        $entity = $ev->getEntity();

        if ($entity instanceof Player) {
            $from = $ev->getFrom()->getWorld();

            $to = $ev->getTo()->getWorld();

            $removeTask = new SendTextsTask($entity, $from, SendType::REMOVE);

            $addTask = new SendTextsTask($entity, $to, SendType::ADD);

            $scheduler = Main::getInstance()->getScheduler();

            $scheduler->scheduleDelayedRepeatingTask($removeTask, SendTextsTask::DELAY_TICKS, SendTextsTask::TICKING_PERIOD);
            $scheduler->scheduleDelayedRepeatingTask($addTask, SendTextsTask::DELAY_TICKS, SendTextsTask::TICKING_PERIOD);
        }
    }

    /**
     * @param PlayerChatEvent $ev
     */
    public function onPlayerChat(PlayerChatEvent $ev): void
    {
        $player = $ev->getPlayer();

        $configurator = Main::getInstance()->getCrateManager()->getConfiguration($player->getName());

        if ($configurator !== null) {
            switch ($configurator->getType()) {
                case CrateManager::CONFIGURATOR_ITEM_CHANCE:
                    $msg = $ev->getMessage();

                    if (!is_numeric($msg)) {
                        $player->sendMessage(TextFormat::RED . "Use a numeric value for chance");

                        $ev->cancel();

                        return;
                    }

                    if (intval($msg) > 100) {
                        $player->sendMessage(TextFormat::RED . "The maximum value is 100");

                        $ev->cancel();

                        return;
                    }

                    $configurator->setChance(intval($msg));
                    $configurator->save();

                    Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                    $player->sendMessage(Translation::getInstance()->getMessage("SETUP_SUCCESS_CHANCE", ["{VALUE}" => $msg]));

                    $ev->cancel();
                    break;
                case CrateManager::CONFIGURATOR_ITEM_COMMAND:
                    $msg = $ev->getMessage();

                    $commands = explode(",", $msg);

                    $configurator->setCommand($commands);
                    $configurator->save();

                    Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                    $player->sendMessage(Translation::getInstance()->getMessage("SETUP_SUCCESS_COMMAND", ["{VALUE}" => $msg]));

                    $ev->cancel();
                    break;
                case CrateManager::CONFIGURATOR_CRATE_HOLOGRAMS:
                    $msg = str_replace("{LINE}", TextFormat::EOL, TextFormat::colorize($ev->getMessage()));

                    $crate = $configurator->getCrate();

                    $crate->setFloatingText($msg);

                    $crate->updateFloatingText(true, SendType::EDIT);

                    Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                    $player->sendMessage(Translation::getInstance()->getMessage("SETUP_SUCCESS_HOLOGRAM", ["{VALUE}" => $msg]));

                    $ev->cancel();
                    break;
                case CrateManager::CONFIGURATOR_CRATE_COMMANDS:
                    $msg = $ev->getMessage();

                    $commands = explode(",", $msg);

                    $configurator->getCrate()->setCommands($commands);

                    Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                    $player->sendMessage(Translation::getInstance()->getMessage("SETUP_SUCCESS_CRATE_COMMAND", ["{VALUE}" => $msg]));

                    $ev->cancel();
                    break;
                case CrateManager::CONFIGURATOR_CRATE_PARTICLE:
                    $msg = $ev->getMessage();

                    if (is_string($msg) and strtolower($msg) === "rgb") {
                        $configurator->getCrate()->setParticleId(-1);

                        Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                        $player->sendMessage(Translation::getInstance()->getMessage("SETUP_SUCCESS_CRATE_PARTICLE", ["{VALUE}" => $msg]));
                    } else if (is_numeric($msg)) {
                        $id = intval($msg);

                        if ($id > 89) {
                            $id = 89;
                        }

                        $configurator->getCrate()->setParticleId($id);

                        Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                        $player->sendMessage(Translation::getInstance()->getMessage("SETUP_SUCCESS_CRATE_PARTICLE", ["{VALUE}" => $msg]));
                    } else {
                        $player->sendMessage(TextFormat::RED . "Use a numeric value for ID");
                    }

                    $ev->cancel();
                    break;
                case CrateManager::CONFIGURATOR_CRATE_PARTICLE_COLOR:
                    $msg = $ev->getMessage();

                    $configurator->getCrate()->setParticleColor($msg);

                    Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                    $player->sendMessage(Translation::getInstance()->getMessage("SETUP_SUCCESS_CRATE_PARTICLE_COLOR", ["{VALUE}" => $msg]));

                    $ev->cancel();
                    break;
            }
        }
    }

    /**
     * @param BlockBreakEvent $ev
     */
    public function onBreak(BlockBreakEvent $ev): void
    {
        $block = $ev->getBlock();

        if ($ev->isCancelled()) return;

        $crate = Main::getInstance()->getCrateManager()->getCrateByPosition($block->getPosition());

        if ($crate instanceof Crate) {
            $ev->cancel();
        }
    }

    /**
     * @param PlayerInteractEvent $ev
     */
    public function onInteract(PlayerInteractEvent $ev): void
    {
        $player = $ev->getPlayer();

        $block = $ev->getBlock();

        $item = $player->getInventory()->getItemInHand();

        $pluginConfig = Main::getInstance()->getConfig()->get("crates");

        $stringToItem = StringToItemParser::getInstance();

        $id = $stringToItem->lookupAliases($block->asItem())[0] ?? "air";

        if (Main::getInstance()->getCrateManager()->isConfigurator($player->getName())) {
            $configurator = Main::getInstance()->getCrateManager()->getConfiguration($player->getName());

            if ($ev->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK and $configurator->getType() === CrateManager::CONFIGURATOR_SET_CRATE and in_array($id, $pluginConfig["blocks"])) {
                if (Main::getInstance()->getCrateManager()->getCrateByPosition($block->getPosition()) === null) {
                    $crate = $configurator->getCrate();

                    Main::getInstance()->getPositionManager()->createPosition($crate->getName(), $block->getPosition());

                    $crate->updateFloatingText(true, SendType::ADD);

                    Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                    $player->sendMessage(Translation::getInstance()->getMessage("CRATE_SET_BLOCK", [
                        "{PREFIX}" => Main::PREFIX,
                        "{CRATE}" => $crate->getName()
                    ]));

                    $ev->cancel();
                }
            }
        } else if (in_array($id, $pluginConfig["blocks"])) {
            $crate = Main::getInstance()->getCrateManager()->getCrateByPosition($block->getPosition());

            if ($crate instanceof Crate) {
                $ev->cancel();

                if ($ev->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK and $crate->isValidKey($item)) {
                    $crate->openCrate($player, $item);
                } else if ($ev->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
                    $crate->previewCrate($player);

                    $block->getPosition()->getWorld()->addParticle($block->getPosition()->asVector3(), new BlockBreakParticle($block));
                }
            }
        }

        if ($item->getNamedTag()->getTag("KeyType") !== null) $ev->cancel();
    }

    /**
     * @param EntityItemPickupEvent $ev
     */
    public function onPickip(EntityItemPickupEvent $ev): void
    {
        $player = $ev->getEntity();

        $item = $ev->getItem();

        if ($player instanceof Player and $item->getNamedTag()->getTag("CrateItem") !== null) $ev->cancel();
    }
}
