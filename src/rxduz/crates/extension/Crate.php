<?php

namespace rxduz\crates\extension;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\color\Color;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\utils\TextFormat;
use rxduz\crates\CrateManager;
use rxduz\crates\libs\floatingtext\CrateHologramEntity;
use rxduz\crates\Main;
use rxduz\crates\task\OpenAnimationTask;
use rxduz\crates\translation\Translation;
use rxduz\crates\utils\particle\CustomParticle;
use rxduz\crates\utils\particle\DusterParticle;
use rxduz\crates\utils\Utils;

class Crate
{

    /** @var Item[] $items */
    private array $items;

    /** @var bool $open */
    private bool $open;

    /** @var int $dropTime */
    private int $dropTime;

    /** @var CrateHologramEntity|null $entityHologram */
    private CrateHologramEntity|null $entityHologram;

    /** @var int $particleCounter */
    private int $particleCounter;

    public function __construct(private string $name, private array $drops, private array $commands, private string $floatingText, private int $particleId, private string $particleColor)
    {
        $this->items = [];

        $this->open = false;

        $this->dropTime = Main::getInstance()->getDataProvider()->getPluginConfig()["crates"]["drop-item-time"] ?? 3;

        $this->entityHologram = null;

        $this->particleCounter = 0;

        foreach ($this->drops as $slot => $data) {
            $this->items[$slot] = Utils::legacyStringJsonDeserialize($data);
        }

        $this->updateHologram();
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
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;

        $this->save();
    }

    /**
     * @return array
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @param array $commands
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
        $data = explode(":", $this->particleColor);

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
     * @param bool $status
     */
    public function setOpen(bool $status): void
    {
        $positionManager = Main::getInstance()->getPositionManager();

        if (!$positionManager->exists($this->name)) return;

        $position = $positionManager->getPositionByName($this->name);

        $position->getWorld()->broadcastPacketToViewers($position, BlockEventPacket::create(BlockPosition::fromVector3($position->asVector3()), 1, ($status ? 1 : 0)));

        $this->open = $status;
    }

    /**
     * @param Player $player
     * @param int $amount
     */
    public function giveKey(Player $player, int $amount = 0): void
    {
        $pluginConfig = Main::getInstance()->getDataProvider()->getPluginConfig();

        $item = StringToItemParser::getInstance()->parse($pluginConfig["keys"]["id"]);

        if ($item === null) {
            $item = VanillaItems::PAPER();
        }

        $item->setCount($amount);

        $item->setCustomName(TextFormat::colorize(str_replace("{CRATE}", $this->getName(), $pluginConfig["keys"]["name"])));

        $item->setLore([TextFormat::colorize(str_replace("{CRATE}", $this->getName(), $pluginConfig["keys"]["lore"]))]);

        $item->getNamedTag()->setString("KeyType", $this->getName());

        if ($player->getInventory()->canAddItem($item)) {
            $player->getInventory()->addItem($item);
        } else {
            $player->dropItem($item);
        }
    }

    /**
     * @param Item $item
     * @return bool
     */
    public function isValidKey(Item $item): bool
    {
        $stringToItem = StringToItemParser::getInstance();

        $id = $stringToItem->lookupAliases($item)[0] ?? "air";

        return ($id === Main::getInstance()->getDataProvider()->getPluginConfig()["keys"]["id"] and
            $item->getNamedTag()->getTag("KeyType") !== null and
            $item->getNamedTag()->getString("KeyType") === $this->getName()
        );
    }

    /**
     * @param Item $item
     * @param int $chance
     * @return Item
     */
    public function setChanceToItem(Item $item, int $chance): Item
    {
        $item->getNamedTag()->setInt("chance", $chance);

        return $item;
    }

    /**
     * @param Item $item
     * @return int
     */
    public function getChanceToItem(Item $item): int
    {
        if ($item->getNamedTag()->getTag("chance") !== null) {
            return $item->getNamedTag()->getInt("chance");
        }

        return 10;
    }

    /**
     * @param Item $item
     * @param array $commands
     * @return Item
     */
    public function setCommandsToItem(Item $item, array $commands): Item
    {
        $str = implode(",", $commands);

        $item->getNamedTag()->setString("type", "command");

        $item->getNamedTag()->setString("commands", $str);

        return $item;
    }

    /**
     * @param Item $item
     * @return array
     */
    public function getCommandsToItem(Item $item): array
    {
        if ($item->getNamedTag()->getTag("type") !== null and $item->getNamedTag()->getString("type") === "command") {
            $str = $item->getNamedTag()->getString("commands", "");

            return explode(",", $str);
        }

        return [];
    }

    /**
     * @param Item $item
     * @return bool
     */
    public function isValidItem(Item $other): bool
    {
        foreach ($this->items as $slot => $item) {
            if ($item->equalsExact($other)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getDrops(): array
    {
        $drops = [];

        foreach ($this->items as $slot => $item) {
            $chance = $this->getChanceToItem($item);

            $commands = $this->getCommandsToItem($item);

            $drops[] = [
                "item" => $item,
                "type" => (empty($commands) ? "item" : "command"),
                "commands" => $commands,
                "chance" => $chance
            ];
        }

        return $drops;
    }

    /**
     * @return array
     */
    public function getDrop(): array
    {
        $dropTable = [];

        foreach ($this->getDrops() as $drop) {
            for ($i = 0; $i < $drop["chance"]; $i++) {
                $dropTable[] = $drop;
            }
        }

        $randomDrop = $dropTable[array_rand($dropTable)];

        return $randomDrop;
    }

    /**
     * @param Player $player
     */
    public function previewCrate(Player $player): void
    {
        $drops = $this->getDrops();

        $chances = 0;

        foreach ($drops as $crateItem) {
            $chances += $crateItem["chance"];
        }

        $menu = InvMenu::create(count($drops) > 27 ? InvMenuTypeIds::TYPE_DOUBLE_CHEST : InvMenuTypeIds::TYPE_CHEST);

        $menu->setListener(InvMenu::readonly());
        $menu->setName(Translation::getInstance()->getMessage("CRATE_NAME_INVENTORY", ["{CRATE}" => $this->getName()]));

        $slot = 0;

        foreach ($drops as $crateItem) {
            if ($slot > 53) break; // Maximum supported preview items is 54, meaning lowest chances are not shown.
            $item = clone $crateItem["item"];
            $item->setCustomName(Translation::getInstance()->getMessage("CRATE_ITEM_NAME_INVENTORY", ["{NAME}" => $item->getName(), "{COUNT}" => $item->getCount()]));
            $item->setLore([TextFormat::RESET, Translation::getInstance()->getMessage("CRATE_ITEM_LORE_INVENTORY", ["{CHANCE}" => round(($crateItem["chance"] / $chances) * 100, 2, PHP_ROUND_HALF_UP)]), TextFormat::RESET]);
            $menu->getInventory()->setItem($slot, $item);
            $slot++;
        }

        $menu->send($player);
    }

    /**
     * @param Player $player
     * @param Item $key
     */
    public function openCrate(Player $player, Item $key): void
    {
        if (empty($this->getDrops())) {
            $player->sendTip(Translation::getInstance()->getMessage("CRATE_EMPTY_DROPS"));

            return;
        }

        if ($this->isOpen()) {
            $player->sendTip(Translation::getInstance()->getMessage("CRATE_CURRENTLY_OPEN"));

            return;
        }

        $pluginConfig = Main::getInstance()->getDataProvider()->getPluginConfig()["crates"];

        $drop = $this->getDrop();

        $item = clone $drop["item"];

        $player->getInventory()->removeItem($key->setCount(1));

        if ($pluginConfig["animation"]) {
            $this->setOpen(true);

            Main::getInstance()->getScheduler()->scheduleRepeatingTask(new OpenAnimationTask($this, $player, $pluginConfig["duration"], $drop), 8);

            return;
        }

        if ($drop["type"] === "item") {
            if ($player->getInventory()->canAddItem($item)) {
                $player->getInventory()->addItem($item);
            } else {
                $player->getWorld()->dropItem($player->getPosition()->asVector3(), $item);
            }
        }

        foreach ($drop["commands"] as $dropCommand) {
            $player->getServer()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), str_replace("{PLAYER}", '"' . $player->getName() . '"', $dropCommand));
        }

        foreach ($this->getCommands() as $crateCommand) {
            $player->getServer()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), str_replace("{PLAYER}", '"' . $player->getName() . '"', $crateCommand));
        }

        $player->sendTip(Translation::getInstance()->getMessage("CRATE_OPEN_REWARD", ["{CRATE}" => $this->getName(), "{REWARD}" => $item->getName()]));
    }

    public function updatePreview(): void
    {
        $pluginConfig = Main::getInstance()->getDataProvider()->getPluginConfig();

        if (!$pluginConfig["crates"]["preview-items"]) return;

        $cratePosition = Main::getInstance()->getPositionManager()->getPositionByName($this->getName());

        if ($this->dropTime === 0) {
            if ($cratePosition !== null and !empty($this->getDrops())) {
                $drop = $this->getDrops()[array_rand($this->getDrops())];

                /** @var Item $item */
                $item = clone $drop["item"];

                Utils::clearItems($this->getName());

                $item->getNamedTag()->setString("CrateItem", $this->getName());

                $cratePosition->getWorld()->dropItem($cratePosition->add(0.5, 1, 0.5), $item, new Vector3(0, 0, 0));
            }

            $this->dropTime = $pluginConfig["crates"]["drop-item-time"] ?? 3;
        }

        $this->dropTime--;
    }

    public function updateParticles(): void
    {
        $pluginConfig = Main::getInstance()->getDataProvider()->getPluginConfig();

        if (!$pluginConfig["crates"]["particle"]) return;

        $cratePosition = Main::getInstance()->getPositionManager()->getPositionByName($this->getName());

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

    public function updateHologram(): void
    {
        if ($this->getFloatingText() === "") return;

        $found = null;

        $pluginConfig = Main::getInstance()->getDataProvider()->getPluginConfig();

        $cratePosition = Main::getInstance()->getPositionManager()->getPositionByName($this->getName());

        if ($cratePosition !== null) {
            $up = ($pluginConfig["crates"]["preview-items"] ? 2.3 : 1.8); // Blocks to up

            $vector = $cratePosition->asVector3()->add(0.5, $up, 0.5);

            foreach ($cratePosition->getWorld()->getEntities() as $entity) {
                if ($entity instanceof CrateHologramEntity and $entity->crateName !== null and $entity->crateName === $this->getName()) {
                    $found = $entity; // If exists ignore create
                }
            }

            if ($found === null) {
                $entityHologram = new CrateHologramEntity(Location::fromObject($vector, $cratePosition->getWorld()));

                $entityHologram->crateName = $this->getName();

                $entityHologram->spawnToAll();

                $this->entityHologram = $entityHologram;
            } else {
                $this->entityHologram = $found;

                $this->entityHologram->teleport($vector);
            }
        }

        if ($this->entityHologram !== null) $this->entityHologram->setNameTag($this->getFloatingText());
    }

    public function close(): void
    {
        if ($this->entityHologram !== null) {
            $this->entityHologram->close();
        }

        Utils::clearItems($this->getName());

        $this->entityHologram = null;

        $this->open = false;
    }

    public function save(): void
    {
        $data = CrateManager::DEFAULT_DATA;

        foreach ($this->items as $slot => $item) {
            $data["drops"][$slot] = Utils::jsonSerialize($item);
        }

        $data["commands"] = $this->commands;
        $data["floating-text"] = $this->floatingText;
        $data["particle-id"] = $this->particleId;
        $data["particle-color"] = $this->particleColor;

        Main::getInstance()->getCrateManager()->save($this->name, $data);
    }
}
