<?php

namespace rxduz\crates\extension;

use muqsit\invmenu\InvMenu;
use pocketmine\color\Color;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\item\StringToItemParser;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use rxduz\crates\CrateManager;
use rxduz\crates\extension\animations\OpeningAnimationUtils;
use rxduz\crates\libs\texter\FloatingText;
use rxduz\crates\libs\texter\SendType;
use rxduz\crates\Main;
use rxduz\crates\translation\Translation;
use rxduz\crates\utils\particle\CustomParticle;
use rxduz\crates\utils\particle\DusterParticle;
use rxduz\crates\utils\Utils;

final class Crate
{
    /** @var bool $open */
    private bool $open;

    /** @var int $dropTime */
    private int $dropTime;

    /** @var FloatingText|null $floatingTextHologram */
    private FloatingText|null $floatingTextHologram;

    /** @var int $particleCounter */
    private int $particleCounter;

    /**
     * @param string $name
     * @param array<int, Drop> $drops
     * @param string[] $commands
     * @param string $floatingText
     * @param bool $particleEnabled
     * @param int $particleId
     * @param string $particleColor
     * @param string $openingAnimation
     */
    public function __construct(
        private string $name,
        private array $drops,
        private array $commands,
        private string $floatingText,
        private bool $particleEnabled,
        private int $particleId,
        private string $particleColor,
        private string $openingAnimation
    ) {
        $this->open = false;

        $this->dropTime = Main::getInstance()->getConfig()->getNested('crates.drop-item-time', 5);

        $this->floatingTextHologram = null;

        $this->particleCounter = 0;

        $this->updateFloatingText();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->open;
    }

    /**
     * @return Position|null
     */
    public function getPosition(): Position|null
    {
        return Main::getInstance()->getPositionManager()->getPositionByName($this->getName());
    }

    /**
     * @return FloatingText|null
     */
    public function getFloatingTextHologram(): FloatingText|null
    {
        return $this->floatingTextHologram;
    }

    /**
     * @param array<int, Drop>
     */
    public function setDrops(array $drops): void
    {
        $this->drops = $drops;

        $this->save();
    }

    /**
     * @return array<int, Drop>
     */
    public function getDrops(): array
    {
        return $this->drops;
    }

    /**
     * @param int $amount
     * 
     * @return Drop[]
     */
    public function getDrop(int $amount = 1): array
    {
        $selectedDrops = [];

        foreach ($this->drops as $drop) {
            for ($i = 0; $i < $drop->getChance(); $i++) {
                $selectedDrops[] = $drop;
            }
        }

        shuffle($selectedDrops);

        return array_slice($selectedDrops, 0, $amount);
    }

    /**
     * @return string[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @param string[] $commands
     */
    public function setCommands(array $commands): void
    {
        $this->commands = $commands;

        $this->save();
    }

    /**
     * @return string
     */
    public function getFloatingText(): string
    {
        return $this->floatingText;
    }

    /**
     * @param string $text
     */
    public function setFloatingText(string $text): void
    {
        $this->floatingText = $text;

        $this->save();
    }

    /**
     * @return bool
     */
    public function isParticleEnabled(): bool
    {
        return $this->particleEnabled;
    }

    /**
     * @param bool $value
     */
    public function setParticleEnabled(bool $value): void
    {
        $this->particleEnabled = $value;

        $this->save();
    }

    /**
     * @return int
     */
    public function getParticleId(): int
    {
        return $this->particleId;
    }

    /**
     * @param int $id
     */
    public function setParticleId(int $id): void
    {
        $this->particleId = $id;

        $this->save();
    }

    /**
     * @return array
     */
    public function getParticleColor(): array
    {
        $data = explode(':', $this->particleColor);

        return $data;
    }

    /**
     * @param string $rgb
     */
    public function setParticleColor(string $rgb): void
    {
        $this->particleColor = $rgb;

        $this->save();
    }

    /**
     * @return string
     */
    public function getOpeningAnimation(): string
    {
        return $this->openingAnimation;
    }

    /**
     * @param string $openingAnimation
     */
    public function setOpeningAnimation(string $openingAnimation = 'none'): void
    {
        $this->openingAnimation = $openingAnimation;

        $this->save();
    }

    /**
     * @param bool $status
     */
    public function setOpen(bool $status): void
    {
        $position = $this->getPosition();

        if ($position === null) return;

        $position->getWorld()->broadcastPacketToViewers($position, BlockEventPacket::create(BlockPosition::fromVector3($position->asVector3()), 1, ($status ? 1 : 0)));

        $this->open = $status;
    }

    /**
     * @param int $amount
     * 
     * @return Item
     */
    public function getKey(int $amount = 1): Item
    {
        $pluginConfig = Main::getInstance()->getConfig()->get('keys');

        $item = StringToItemParser::getInstance()->parse($pluginConfig['id']);

        $item->setCount($amount);

        $item->setCustomName(TextFormat::colorize(str_replace('{CRATE}', $this->getName(), $pluginConfig['name'])));

        $item->setLore([TextFormat::colorize(str_replace('{CRATE}', $this->getName(), $pluginConfig['lore']))]);

        $item->getNamedTag()->setString('KeyType', $this->getName());

        return $item;
    }

    /**
     * @param Player $player
     * @param int $amount
     */
    public function giveKey(Player $player, int $amount = 1): void
    {
        $item = $this->getKey($amount);

        if ($player->getInventory()->canAddItem($item)) {
            $player->getInventory()->addItem($item);
        } else {
            $player->dropItem($item);
        }
    }

    /**
     * @param Item $item
     * 
     * @return bool
     */
    public function isValidKey(Item $item): bool
    {
        return ($item->getNamedTag()->getTag('KeyType') !== null and
            $item->getNamedTag()->getString('KeyType') === $this->getName()
        );
    }

    /**
     * @param Player $player
     */
    public function previewCrate(Player $player): void
    {
        $drops = $this->getDrops();

        $chances = 0;

        foreach ($drops as $drop) {
            $chances += $drop->getChance();
        }

        $menu = InvMenu::create(count($drops) > 27 ? InvMenu::TYPE_DOUBLE_CHEST : InvMenu::TYPE_CHEST);

        $menu->setListener(InvMenu::readonly());
        $menu->setName(Translation::getInstance()->getMessage('CRATE_NAME_INVENTORY', ['{CRATE}' => $this->getName()]));

        foreach ($drops as $slot => $drop) {
            $item = clone $drop->getItem();

            $item->setCustomName(Translation::getInstance()->getMessage('CRATE_ITEM_NAME_INVENTORY', ['{NAME}' => $item->getName(), '{COUNT}' => $item->getCount()]));
            $item->setLore([TextFormat::RESET, Translation::getInstance()->getMessage('CRATE_ITEM_LORE_INVENTORY', ['{CHANCE}' => round(($drop->getChance() / $chances) * 100, 2, PHP_ROUND_HALF_UP)]), TextFormat::RESET]);
            $menu->getInventory()->setItem($slot, $item);
        }

        $menu->send($player);
    }

    /**
     * @param Player $player
     * @param Item $key
     * @param bool $skipAnimation
     */
    public function openCrate(Player $player, Item $key, bool $skipAnimation = false): void
    {
        if (empty($this->getDrops())) {
            $player->sendTip(Translation::getInstance()->getMessage('CRATE_EMPTY_DROPS'));

            return;
        }

        if ($this->isOpen()) {
            $player->sendTip(Translation::getInstance()->getMessage('CRATE_CURRENTLY_OPEN'));

            return;
        }

        // New method to skip the animation and open the number of keys you have.
        if ($skipAnimation) {
            $amount = $key->getCount();

            $player->getInventory()->removeItem($key->setCount($amount));

            foreach ($this->getDrop($amount) as $drop) {
                $item = clone $drop->getItem();

                if ($drop->getType() === 'item') {
                    if ($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                    } else {
                        $player->getWorld()->dropItem($player->getPosition()->asVector3(), $item);
                    }
                }

                foreach ($drop->getCommands() as $dropCommand) {
                    $player->getServer()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), str_replace('{PLAYER}', '"' . $player->getName() . '"', $dropCommand));
                }

                foreach ($this->getCommands() as $crateCommand) {
                    $player->getServer()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), str_replace('{PLAYER}', '"' . $player->getName() . '"', $crateCommand));
                }

                $player->sendMessage(Translation::getInstance()->getMessage('CRATE_OPEN_REWARD', ['{CRATE}' => $this->getName(), '{REWARD}' => $item->getName()]));
            }

            return;
        }

        $player->getInventory()->removeItem($key->setCount(1));

        if (OpeningAnimationUtils::getInstance()->exists($this->openingAnimation)) {
            OpeningAnimationUtils::getInstance()->sendAnimationTask($this->openingAnimation, $this, $player);

            return;
        }

        $drop = $this->getDrop(1)[0];

        $item = clone $drop->getItem();

        if ($drop->getType() === 'item') {
            if ($player->getInventory()->canAddItem($item)) {
                $player->getInventory()->addItem($item);
            } else {
                $player->getWorld()->dropItem($player->getPosition()->asVector3(), $item);
            }
        }

        foreach ($drop->getCommands() as $dropCommand) {
            $player->getServer()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), str_replace('{PLAYER}', '"' . $player->getName() . '"', $dropCommand));
        }

        foreach ($this->getCommands() as $crateCommand) {
            $player->getServer()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), str_replace('{PLAYER}', '"' . $player->getName() . '"', $crateCommand));
        }

        $player->sendTip(Translation::getInstance()->getMessage('CRATE_OPEN_REWARD', ['{CRATE}' => $this->getName(), '{REWARD}' => $item->getName()]));
    }

    public function updatePreview(): void
    {
        if (!Main::getInstance()->getConfig()->getNested('crates.preview-items', true)) return;

        $cratePosition = $this->getPosition();

        if ($this->dropTime === 0) {
            if ($cratePosition !== null and !empty($this->getDrops())) {
                $drop = $this->getDrops()[array_rand($this->getDrops())];

                $item = clone $drop->getItem();

                Utils::clearItems($this->getName());

                $item->getNamedTag()->setString('CrateItem', $this->getName());

                $cratePosition->getWorld()->dropItem($cratePosition->add(0.5, 1, 0.5), $item, Vector3::zero());
            }

            $this->dropTime = Main::getInstance()->getConfig()->getNested('crates.drop-item-time', 3);
        }

        $this->dropTime--;
    }

    public function updateParticles(): void
    {
        if (!$this->particleEnabled) return;

        $cratePosition = $this->getPosition();

        if ($cratePosition === null) return;

        $x = $cratePosition->getX() + 0.5;
        $y = $cratePosition->getY();
        $z = $cratePosition->getZ() + 0.5;

        $size = 0.6;

        $a = cos(deg2rad($this->particleCounter / 0.06)) * $size;
        $b = sin(deg2rad($this->particleCounter / 0.06)) * $size;

        $vector1 = new Vector3($x - $a, $y, $z - $b);
        $vector2 = new Vector3($x + $a, $y, $z + $b);

        $id = $this->getParticleId();

        if ($id === -1) {
            $color = $this->getParticleColor();

            $cratePosition->getWorld()->addParticle($vector1, new DusterParticle(new Color($color[0] ?? 0, $color[1] ?? 255, $color[2] ?? 0)));
            $cratePosition->getWorld()->addParticle($vector2, new DusterParticle(new Color($color[0] ?? 0, $color[1] ?? 255, $color[2] ?? 0)));
        } else {
            $cratePosition->getWorld()->addParticle($vector1, new CustomParticle($id));
            $cratePosition->getWorld()->addParticle($vector2, new CustomParticle($id));
        }

        $this->particleCounter++;

        if ($this->particleCounter > 200) {
            $this->particleCounter = 0;
        }
    }

    /**
     * @param bool $updatePlayers
     * @param SendType $sendType
     */
    public function updateFloatingText(bool $updatePlayers = false, SendType $sendType = SendType::ADD): void
    {
        $position = $this->getPosition();

        if ($this->getFloatingText() === '' or $position === null) return;

        $previewItems = Main::getInstance()->getConfig()->getNested('crates.preview-items');

        $blocksToUp = ($previewItems ? 2.1 : 1);

        if ($updatePlayers) {
            if ($this->floatingTextHologram === null) {
                $this->floatingTextHologram = new FloatingText($position->asVector3()->add(0.5, $blocksToUp, 0.5), $this->getFloatingText(), Entity::nextRuntimeId());

                foreach ($position->getWorld()->getViewersForPosition($position->asVector3()) as $player) {
                    $this->floatingTextHologram->sendToPlayer($player, SendType::ADD);
                }

                return;
            }

            if ($sendType === SendType::EDIT) {
                $this->floatingTextHologram->setText($this->getFloatingText()); // Update text :)
            }

            foreach ($position->getWorld()->getViewersForPosition($position->asVector3()) as $player) {
                $this->floatingTextHologram->sendToPlayer($player, $sendType);
            }
        } else {
            $this->floatingTextHologram = new FloatingText($position->asVector3()->add(0.5, $blocksToUp, 0.5), $this->getFloatingText(), Entity::nextRuntimeId());
        }
    }

    public function close(): void
    {
        Utils::clearItems($this->getName());

        $position = $this->getPosition();

        if ($position !== null) {
            foreach ($position->getWorld()->getViewersForPosition($position->asVector3()) as $player) {
                if ($this->floatingTextHologram !== null) $this->floatingTextHologram->sendToPlayer($player, SendType::REMOVE);
            }
        }

        $this->open = false;
    }

    public function save(): void
    {
        $data = CrateManager::DEFAULT_DATA;

        $drops = [];

        foreach ($this->drops as $slot => $drop) {
            $drops[$slot] = [
                'item' => Utils::jsonSerialize($drop->getItem()),
                'type' => $drop->getType(),
                'commands' => $drop->getCommands(),
                'chance' => $drop->getChance()
            ];
        }

        $data['drops'] = $drops;
        $data['commands'] = $this->commands;
        $data['floating-text'] = $this->floatingText;
        $data['particle-enabled'] = $this->particleEnabled;
        $data['particle-id'] = $this->particleId;
        $data['particle-color'] = $this->particleColor;
        $data['opening-animation'] = $this->openingAnimation;

        Main::getInstance()->getCrateManager()->save($this->name, $data);
    }
}
