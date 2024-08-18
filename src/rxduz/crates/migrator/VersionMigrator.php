<?php

namespace rxduz\crates\migrator;

use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\StringToItemParser;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use rxduz\crates\extension\Crate;
use rxduz\crates\Main;

class VersionMigrator
{

    use SingletonTrait;

    /** @var Config $data */
    private Config $data;

    public function init(): void
    {
        $this->data = new Config(Main::getInstance()->getDataFolder() . "/crates_old.yml", Config::YAML);

        $crateManager = Main::getInstance()->getCrateManager();

        $count = 0;

        foreach ($this->data->get("crates", []) as $crateName => $value) {
            $crateManager->createCrate($crateName);

            $crate = $crateManager->getCrateByName($crateName);

            if ($crate instanceof Crate) {
                $crate->setFloatingText($value["floating-text"]);

                $crate->setCommands($value["commands"]);

                $crate->setItems($this->getDrops($crate, $value["drops"]));

                $count++;
            }
        }

        Main::getInstance()->getLogger()->info(Main::PREFIX . TextFormat::GREEN . "Migrated " . $count . " crates successfully!");
    }

    /**
     * @param Crate $crate
     * @param array $data
     * @return array
     */
    public function getDrops(Crate $crate, array $data): array
    {
        $drops = [];

        $slot = 0;

        foreach ($data as $key => $value) {
            $item = StringToItemParser::getInstance()->parse($value["id"]);

            if ($item === null) {
                Main::getInstance()->getLogger()->info(Main::PREFIX . TextFormat::GREEN . "Item " . $value["id"] . " is NULL");

                continue;
            }

            $item->setCount($value["amount"]);

            if (isset($value["name"])) {
                $item->setCustomName(TextFormat::colorize($value["name"]));
            }

            if (isset($value["lore"])) {
                $item->setLore([TextFormat::colorize($value["lore"])]);
            }

            if (isset($value["enchantments"])) {
                $enchantments = $value["enchantments"];

                foreach ($enchantments as $k => $v) {
                    $enchantment = null;

                    if (is_int($v["name"])) {
                        $enchantment = EnchantmentIdMap::getInstance()->fromId($v["name"]);
                    }

                    if (is_string($v["name"])) {
                        $enchantment = StringToEnchantmentParser::getInstance()->parse($v["name"]);
                    }

                    $level = $v["level"] ?? 1;

                    if ($enchantment instanceof Enchantment) {
                        $item->addEnchantment(new EnchantmentInstance($enchantment, $level));
                    }
                }
            }

            $type = $value["type"] ?? "item";

            if ($type === "command") {
                $item = $crate->setCommandsToItem($item, $value["commands"]);
            }

            $chance = $value["chance"] ?? 10;

            if ($chance !== 10) {
                $item = $crate->setChanceToItem($item, intval($chance));
            }

            $drops[$slot] = $item;

            $slot++;
        }

        return $drops;
    }
}
