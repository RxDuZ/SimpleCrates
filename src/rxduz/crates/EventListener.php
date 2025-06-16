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
use rxduz\crates\extension\animations\OpeningAnimationUtils;
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

        if ($entity->getItem()->getNamedTag()->getTag('CrateItem') !== null) {
            $entity->setNameTag(Translation::getInstance()->getMessage('CRATE_ITEM_NAME_INVENTORY', ['{NAME}' => $entity->getItem()->getName(), '{COUNT}' => $entity->getItem()->getCount()]));
            $entity->setNameTagAlwaysVisible();
        }
    }

    /**
     * @param PlayerJoinEvent $ev
     */
    public function onPlayerJoin(PlayerJoinEvent $ev): void
    {
        $player = $ev->getPlayer();

        $sendTask = new SendTextsTask($player, $player->getWorld(), SendType::ADD);

        Main::getInstance()->getScheduler()->scheduleDelayedRepeatingTask($sendTask, SendTextsTask::DELAY_TICKS, SendTextsTask::TICKING_PERIOD);
    }

    /**
     * @param EntityTeleportEvent $ev
     */
    public function onEntityTeleport(EntityTeleportEvent $ev): void
    {
        $player = $ev->getEntity();

        if (!$player instanceof Player or !$player->isOnline()) return;

        $from = $ev->getFrom()->getWorld();

        $to = $ev->getTo()->getWorld();

        if ($from->getFolderName() === $to->getFolderName()) return;

        $removeTask = new SendTextsTask($player, $from, SendType::REMOVE);

        $addTask = new SendTextsTask($player, $to, SendType::ADD);

        $scheduler = Main::getInstance()->getScheduler();

        $scheduler->scheduleDelayedRepeatingTask($removeTask, SendTextsTask::DELAY_TICKS, SendTextsTask::TICKING_PERIOD);
        $scheduler->scheduleDelayedRepeatingTask($addTask, SendTextsTask::DELAY_TICKS, SendTextsTask::TICKING_PERIOD);
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
                case CrateManager::CONFIGURATOR_ITEM_CHANGE:
                    $item = $player->getInventory()->getItemInHand();

                    $message = strtolower($ev->getMessage());

                    if ($message === 'confirm') {
                        if ($item->isNull()) {
                            $player->sendMessage(TextFormat::RED . 'Please use a valid item.');

                            $ev->cancel();

                            return;
                        }

                        $configurator->setItem($item);

                        Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                        $player->sendMessage(Translation::getInstance()->getMessage('SETUP_SUCCESS_CHANGE', ['{ITEM}' => $item->getName()]));

                        $ev->cancel();
                    } else if ($message === 'cancel') {
                        Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                        $player->sendMessage(Translation::getInstance()->getMessage('COMMAND_SETUP_CANCEL'));

                        $ev->cancel();
                    }
                    break;
                case CrateManager::CONFIGURATOR_ITEM_CHANCE:
                    $message = $ev->getMessage();

                    if (!is_numeric($message)) {
                        $player->sendMessage(TextFormat::RED . 'Use a numeric value for chance.');

                        $ev->cancel();

                        return;
                    }

                    $chance = intval($message);

                    if ($chance > 100) {
                        $player->sendMessage(TextFormat::RED . 'The maximum value is 100.');

                        $ev->cancel();

                        return;
                    }

                    $configurator->setChance($chance);

                    Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                    $player->sendMessage(Translation::getInstance()->getMessage('SETUP_SUCCESS_CHANCE', ['{VALUE}' => $chance]));

                    $ev->cancel();
                    break;
                case CrateManager::CONFIGURATOR_ITEM_COMMAND:
                    $message = $ev->getMessage();

                    if (strtolower($message) === 'none') {
                        $configurator->removeCommand();
                    } else {
                        $commands = explode(',', $message);

                        $configurator->setCommand($commands);
                    }

                    Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                    $player->sendMessage(Translation::getInstance()->getMessage('SETUP_SUCCESS_COMMAND', ['{VALUE}' => $message]));

                    $ev->cancel();
                    break;
                case CrateManager::CONFIGURATOR_CRATE_HOLOGRAMS:
                    $message = str_replace('{LINE}', TextFormat::EOL, TextFormat::colorize($ev->getMessage()));

                    $crate = $configurator->getCrate();

                    $crate->setFloatingText($message);

                    $crate->updateFloatingText(true, SendType::EDIT);

                    Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                    $player->sendMessage(Translation::getInstance()->getMessage('SETUP_SUCCESS_HOLOGRAM', ['{VALUE}' => $message]));

                    $ev->cancel();
                    break;
                case CrateManager::CONFIGURATOR_CRATE_COMMANDS:
                    $message = $ev->getMessage();

                    $crate = $configurator->getCrate();

                    if (strtolower($message) === 'none') {
                        $crate->setCommands([]);
                    } else {
                        $commands = explode(',', $message);

                        $crate->setCommands($commands);
                    }

                    Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                    $player->sendMessage(Translation::getInstance()->getMessage('SETUP_SUCCESS_CRATE_COMMAND', ['{VALUE}' => $message]));

                    $ev->cancel();
                    break;
                case CrateManager::CONFIGURATOR_CRATE_PARTICLE:
                    $message = $ev->getMessage();

                    $crate = $configurator->getCrate();

                    if (is_string($message) and strtolower($message) === 'rgb') {
                        $crate->setParticleId(-1);

                        Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                        $player->sendMessage(Translation::getInstance()->getMessage('SETUP_SUCCESS_CRATE_PARTICLE', ['{VALUE}' => 'RGB']));
                    } else if (is_numeric($message)) {
                        $id = intval($message);

                        if ($id > 89) {
                            $id = 89;
                        }

                        $crate->setParticleId($id);

                        Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                        $player->sendMessage(Translation::getInstance()->getMessage('SETUP_SUCCESS_CRATE_PARTICLE_ID', ['{VALUE}' => $id]));
                    } else {
                        $player->sendMessage(TextFormat::RED . 'Use a numeric value for ID');
                    }

                    $ev->cancel();
                    break;
                case CrateManager::CONFIGURATOR_CRATE_PARTICLE_COLOR:
                    $message = $ev->getMessage();

                    $args = explode(':', $message);

                    if (!isset($args[0], $args[1], $args[2])) {
                        $player->sendMessage(TextFormat::RED . 'Arguments are missing, example: 255:0:0');

                        $ev->cancel();

                        return;
                    }

                    if (!is_numeric($args[0]) or !is_numeric($args[1]) or !is_numeric($args[2])) {
                        $player->sendMessage(TextFormat::RED . 'Use numeric value in the RGB format.');

                        $ev->cancel();

                        return;
                    }

                    $configurator->getCrate()->setParticleColor($message);

                    Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                    $player->sendMessage(Translation::getInstance()->getMessage('SETUP_SUCCESS_CRATE_PARTICLE_COLOR', ['{VALUE}' => $message]));

                    $ev->cancel();
                    break;
                case CrateManager::CONFIGURATOR_CRATE_OPENING_ANIMATION:
                    $message = strtolower($ev->getMessage());

                    $crate = $configurator->getCrate();

                    if ($message === 'none') {
                        $crate->setOpeningAnimation();

                        Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                        $player->sendMessage(Translation::getInstance()->getMessage('SETUP_DISABLED_CRATE_OPENING_ANIMATION'));

                        $ev->cancel();

                        return;
                    }

                    if (!OpeningAnimationUtils::getInstance()->exists($message)) {
                        $player->sendMessage(TextFormat::RED . 'This animation does not exist, available animations: ' . TextFormat::WHITE . implode(', ', OpeningAnimationUtils::getInstance()->getAnimationsLocal()));

                        $ev->cancel();

                        return;
                    }

                    $crate->setOpeningAnimation($message);

                    Main::getInstance()->getCrateManager()->removeConfigurator($player->getName());

                    $player->sendMessage(Translation::getInstance()->getMessage('SETUP_SUCCESS_CRATE_OPENING_ANIMATION', ['{VALUE}' => $message]));

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
        if (Main::getInstance()->getCrateManager()->getCrateByPosition($ev->getBlock()->getPosition()) instanceof Crate) $ev->cancel();
    }

    /**
     * @param PlayerInteractEvent $ev
     */
    public function onInteract(PlayerInteractEvent $ev): void
    {
        $crateManager = Main::getInstance()->getCrateManager();
        $player = $ev->getPlayer();
        $block = $ev->getBlock();
        $item = $player->getInventory()->getItemInHand();
        $pluginConfig = Main::getInstance()->getConfig()->get('crates');
        $id = StringToItemParser::getInstance()->lookupAliases($block->asItem())[0] ?? 'air';

        if ($crateManager->isConfigurator($player->getName())) {
            $configurator = $crateManager->getConfiguration($player->getName());
            if ($ev->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK and $configurator->getType() === CrateManager::CONFIGURATOR_SET_CRATE and in_array($id, $pluginConfig['blocks'])) {
                if ($crateManager->getCrateByPosition($block->getPosition()) === null) {
                    $crate = $configurator->getCrate();
                    Main::getInstance()->getPositionManager()->createPosition($crate->getName(), $block->getPosition());
                    $crate->updateFloatingText(true, SendType::ADD);
                    $crateManager->removeConfigurator($player->getName());
                    $player->sendMessage(Translation::getInstance()->getMessage('CRATE_SET_BLOCK', [
                        '{PREFIX}' => Main::PREFIX,
                        '{CRATE}' => $crate->getName()
                    ]));
                    $ev->cancel();
                }
            }
        } elseif (in_array($id, $pluginConfig['blocks'])) {
            $crate = $crateManager->getCrateByPosition($block->getPosition());
            if ($crate instanceof Crate) {
                $ev->cancel();
                if ($ev->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK and $crate->isValidKey($item)) {
                    if ($pluginConfig['skip-animation-mode'] and $player->isSneaking()) {
                        if ($crateManager->inSkipAnimation($player->getName())) {
                            $crate->openCrate($player, $item, true);
                            $crateManager->removeSkipAnimation($player->getName());
                        } else {
                            $crateManager->setSkipAnimation($player->getName());
                            $player->sendMessage(Translation::getInstance()->getMessage('CRATE_CONFIRM_SKIP_ANIMATION'));
                        }
                    } else {
                        $crate->openCrate($player, $item);
                    }
                } elseif ($ev->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
                    $crate->previewCrate($player);
                    $block->getPosition()->getWorld()->addParticle($block->getPosition()->asVector3(), new BlockBreakParticle($block));
                }
            }
        }

        if ($item->getNamedTag()->getTag('KeyType') !== null) $ev->cancel();
    }

    /**
     * @param EntityItemPickupEvent $ev
     */
    public function onPickip(EntityItemPickupEvent $ev): void
    {
        if ($ev->getItem()->getNamedTag()->getTag('CrateItem') !== null) $ev->cancel();
    }
}
