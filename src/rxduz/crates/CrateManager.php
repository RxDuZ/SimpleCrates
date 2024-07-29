<?php

namespace rxduz\crates;

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;
use rxduz\crates\extension\Crate;
use rxduz\crates\libs\floatingtext\CrateHologramEntity;
use rxduz\crates\Main;
use rxduz\crates\utils\Configurator;

class CrateManager
{

    /** @var array */
    public const DEFAULT_DATA = [
        "drops" => [],
        "commands" => [],
        "floating-text" => "",
        "particle-id" => -1,
        "particle-color" => "0:255:0"
    ];

    /** @var string */
    public const CONFIGURATOR_SET_CRATE = "set_crate";

    /** @var string */
    public const CONFIGURATOR_ITEM_CHANCE = "item_chance";

    /** @var string */
    public const CONFIGURATOR_ITEM_COMMAND = "item_command";

    /** @var string */
    public const CONFIGURATOR_CRATE_HOLOGRAMS = "crate_holograms";

    /** @var string */
    public const CONFIGURATOR_CRATE_COMMANDS = "crate_commands";

    /** @var string */
    public const CONFIGURATOR_CRATE_PARTICLE = "crate_particle";

    /** @var string */
    public const CONFIGURATOR_CRATE_PARTICLE_COLOR = "crate_particle_color";

    /** @var Config $data */
    private Config $data;

    /** @var array $setters */
    private array $setters = [];

    /** @var Crate[] $crates */
    private array $crates = [];

    public function __construct()
    {
        $this->data = Main::getInstance()->getDataProvider()->getConfiguration("/crates");

        EntityFactory::getInstance()->register(
            CrateHologramEntity::class,
            function (World $world, CompoundTag $nbt): CrateHologramEntity {
                return new CrateHologramEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
            },
            ['CrateHologramEntity']
        );

        foreach ($this->data->getAll() as $name => $value) {
            $this->crates[$name] = new Crate(
                $name,
                $value["drops"] ?? [],
                $value["commands"] ?? [],
                $value["floating-text"] ?? "",
                $value["particle-id"] ?? -1,
                $value["particle-color"] ?? "0:255:0"
            );
        }
    }

    /**
     * @return Crate[]
     */
    public function getCrates(): array
    {
        return $this->crates;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function exists(string $name): bool
    {
        return isset($this->crates[$name]);
    }

    /**
     * @param Position $position
     * @return Crate|null
     */
    public function getCrateByPosition(Position $position): Crate|null
    {
        foreach ($this->getCrates() as $crate) {
            if (Main::getInstance()->getPositionManager()->exists($crate->getName())) {
                if (Main::getInstance()->getPositionManager()->getPositionByName($crate->getName())->equals($position->asVector3())) {
                    return $crate;
                }
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @return Crate|null
     */
    public function getCrateByName(string $name): Crate|null
    {
        return $this->crates[$name] ?? null;
    }

    /**
     * @param string $crateName
     */
    public function createCrate(string $crateName): void
    {
        $data = self::DEFAULT_DATA;

        $this->data->set($crateName, $data);

        $this->data->save();

        $this->crates[$crateName] = new Crate($crateName, $data["drops"], $data["commands"], $data["floating-text"], $data["particle-id"], $data["particle-color"]);
    }

    /**
     * @param string $crateName
     */
    public function removeCrate(string $crateName): void
    {
        $crate = $this->getCrateByName($crateName);

        if (Main::getInstance()->getPositionManager()->exists($crateName)) {
            Main::getInstance()->getPositionManager()->removePosition($crateName);
        }

        if ($crate !== null) {
            $crate->close();
        }

        $this->data->remove($crateName);
        $this->data->save();

        unset($this->crates[$crateName]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isConfigurator(string $name): bool
    {
        return $this->getConfiguration($name) !== null;
    }

    /**
     * @param Configurator $configurator
     */
    public function setConfigurator(Configurator $configurator): void
    {
        $this->setters[$configurator->getUserName()] = $configurator;
    }

    /**
     * @param string $name
     * @return Configurator|null
     */
    public function getConfiguration(string $name): Configurator|null
    {
        return $this->setters[$name] ?? null;
    }

    /**
     * @param string $name
     */
    public function removeConfigurator(string $name): void
    {
        if (isset($this->setters[$name])) {
            unset($this->setters[$name]);
        }
    }

    /**
     * @param string $crateName
     * @param array $data
     */
    public function save(string $crateName, array $data)
    {
        $this->data->set($crateName, $data);

        $this->data->save();
    }

    public function closeAll(): void
    {
        foreach ($this->crates as $crate) {
            $crate->close();
        }
    }
}
