<?php

namespace rxduz\crates\task;

use muqsit\invmenu\inventory\InvMenuInventory;
use muqsit\invmenu\InvMenu;
use pocketmine\block\StainedGlassPane;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Wool;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\data\bedrock\DyeColorIdMap;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\LavaParticle;
use RuntimeException;
use rxduz\crates\extension\Crate;
use rxduz\crates\extension\Drop;
use rxduz\crates\translation\Translation;
use rxduz\crates\utils\Utils;

class SpinningAnimationTask extends Task
{

    /** @var int */
    private const INVENTORY_ROW_COUNT = 9;

    /** @var int */
    private int $currentTicks = 0;

    /** @var InvMenu */
    private InvMenu $menu;

    /** @var array<int, Drop> */
    private array $lastRewards = [];

    /** @var bool */
    private bool $showReward = false;

    /** @var int[] */
    private array $emptySlots = [0, 1, 2, 3, 5, 6, 7, 8, 18, 19, 20, 21, 23, 24, 25, 26];

    /** @var int */
    private int $color = 0;

    public function __construct(
        private readonly Crate $crate,
        private readonly Player $player,
        private array $templateData,
    ) {
        $this->menu = InvMenu::create(InvMenu::TYPE_CHEST);
        $this->menu->setName(Translation::getInstance()->getMessage('CRATE_NAME_INVENTORY', ['{CRATE}' => $this->crate->getName()]));
        $this->menu->getInventory()->setContents([4 => ($endRod = VanillaBlocks::END_ROD()->asItem()->setCustomName(TextFormat::ITALIC)), 22 => $endRod]);
        $this->menu->setListener(InvMenu::readonly());
        $this->menu->send($player);
    }

    public function onRun(): void
    {
        $player = $this->player;

        if (!$player->isOnline()) {
            $this->crate->setOpen(false);

            if (($handler = $this->getHandler()) !== null) $handler->cancel();

            return;
        }

        $this->currentTicks++;

        $speed = $this->templateData['speed'];
        $safeSpeed = max($speed, 1);
        $duration = $this->templateData['duration'];
        $safeDuration = (($duration / $safeSpeed) >= 5.5) ? $duration : (5.5 * $safeSpeed);

        if ($this->currentTicks >= $safeDuration) {
            if (!$this->showReward) {
                $this->showReward = true;
            } else if ($this->currentTicks - $safeDuration > 20) {
                $drop = $this->lastRewards[floor(self::INVENTORY_ROW_COUNT / 2)] ?? null;

                if ($drop === null) {
                    throw new RuntimeException('Could not find a drop for \'' . $player->getName() . '\'');
                }

                $item = clone $drop->getItem();

                if ($drop->getType() === 'item') {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->dropItem($item);
                    }
                }

                foreach ($drop->getCommands() as $dropCommand) {
                    $player->getServer()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), str_replace('{PLAYER}', '"' . $player->getName() . '"', $dropCommand));
                }

                foreach ($this->crate->getCommands() as $crateCommand) {
                    $player->getServer()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), str_replace('{PLAYER}', '"' . $player->getName() . '"', $crateCommand));
                }

                $player->sendTip(Translation::getInstance()->getMessage('CRATE_OPEN_REWARD', ['{CRATE}' => $this->crate->getName(), '{REWARD}' => $item->getName()]));

                Utils::playSound($player, 'random.explode');

                $cratePosition = $this->crate->getPosition();

                $x = $cratePosition->getX() + 0.5;
                $y = $cratePosition->getY() + 1;
                $z = $cratePosition->getZ() + 0.5;

                $radius = 1;

                for ($i = 0; $i < 20; $i++) {
                    $cx = $x + ($radius * cos($i));

                    $cz = $z + ($radius * sin($i));

                    $position = new Vector3($cx, $y, $cz);

                    $cratePosition->getWorld()->addParticle($position, new LavaParticle(), [$player]);
                }

                $this->crate->setOpen(false);

                $player->removeCurrentWindow();

                if (($handler = $this->getHandler()) !== null) $handler->cancel();
            }

            return;
        }

        if ($this->currentTicks % $safeSpeed === 0) {
            $this->roulette($player);
        }
    }

    /**
     * @param Player $player
     */
    public function roulette(Player $player): void
    {
        $this->lastRewards[self::INVENTORY_ROW_COUNT] = $this->crate->getDrop(1)[0];

        foreach ($this->lastRewards as $slot => $drop) {
            if ($slot !== 0) {
                $this->lastRewards[$slot - 1] = $drop;
                $this->menu->getInventory()->setItem($slot + self::INVENTORY_ROW_COUNT - 1, $drop->getItem());
            }
        }

        $background = StringToItemParser::getInstance()->parse($this->templateData['background']['block'])->getBlock();

        if (strtolower($this->templateData['background']['type']) === 'rainbow' and ($background instanceof StainedGlassPane or $background instanceof Wool)) {
            $background->setColor(DyeColorIdMap::getInstance()->fromId($this->color))->asItem();
        }

        foreach ($this->emptySlots as $emptySlot) {
            $this->menu->getInventory()->setItem($emptySlot, $background->asItem());
        }

        $this->color++;

        if ($this->color > 15) {
            $this->color = 0;
        }

        if ($player->getCurrentWindow() instanceof InvMenuInventory) Utils::playSound($player, 'random.click');
    }
}
