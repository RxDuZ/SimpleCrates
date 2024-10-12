<?php

namespace rxduz\crates;

use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;
use rxduz\crates\extension\Crate;
use rxduz\crates\Main;
use rxduz\crates\utils\Configurator;
use rxduz\crates\utils\Utils;

class CrateManager
{

    /** @var array */
    public const DEFAULT_DATA = [
        'drops' => [],
        'commands' => [],
        'floating-text' => '',
        'particle-id' => -1,
        'particle-color' => '0:255:0'
    ];

    /** @var array */
    public const DEFAULT_ITEM_DATA = [
        'item' => '',
        'type' => 'item',
        'commands' => [],
        'chance' => 10
    ];

    /** @var string */
    public const CONFIGURATOR_SET_CRATE = 'set_crate';

    /** @var string */
    public const CONFIGURATOR_ITEM_CHANGE = 'item_change';

    /** @var string */
    public const CONFIGURATOR_ITEM_CHANCE = 'item_chance';

    /** @var string */
    public const CONFIGURATOR_ITEM_COMMAND = 'item_command';

    /** @var string */
    public const CONFIGURATOR_CRATE_HOLOGRAMS = 'crate_holograms';

    /** @var string */
    public const CONFIGURATOR_CRATE_COMMANDS = 'crate_commands';

    /** @var string */
    public const CONFIGURATOR_CRATE_PARTICLE = 'crate_particle';

    /** @var string */
    public const CONFIGURATOR_CRATE_PARTICLE_COLOR = 'crate_particle_color';

    /** @var Config $data */
    private Config $data;

    /** @var array $setters */
    private array $setters = [];

    /** @var array<string, Crate> $crates */
    private array $crates = [];

    public function __construct()
    {
        $this->data = new Config(Main::getInstance()->getDataFolder() . '/crates.yml', Config::YAML);

        foreach ($this->data->getAll() as $name => $value) {
            $drops = [];

            foreach ($value['drops'] ?? [] as $slot => $drop) {
                $chance = $drop['chance'] ?? 10;

                $commands = $drop['commands'] ?? [];

                $drops[$slot] = [
                    'item' => Utils::legacyStringJsonDeserialize($drop['item']),
                    'type' => (empty($commands) ? 'item' : 'command'),
                    'commands' => $commands,
                    'chance' => $chance
                ];
            }

            $this->crates[strtolower($name)] = new Crate(
                $name,
                $drops,
                $value['commands'] ?? [],
                $value['floating-text'] ?? '',
                $value['particle-id'] ?? -1,
                $value['particle-color'] ?? '0:255:0'
            );
        }
    }

    /**
     * @return array<string, Crate>
     */
    public function getCrates(): array
    {
        return $this->crates;
    }

    /**
     * @return array<string, Crate>
     */
    public function getCratesByWorld(World $world): array
    {
        return array_filter($this->crates, function (Crate $crate) use ($world): bool {
            return ($crate->getPosition() !== null and $crate->getPosition()->getWorld()->getFolderName() === $world->getFolderName());
        });
    }

    /**
     * @param string $name
     * @return bool
     */
    public function exists(string $name): bool
    {
        return isset($this->crates[strtolower($name)]);
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
        return $this->crates[strtolower($name)] ?? null;
    }

    /**
     * @param string $name
     */
    public function createCrate(string $name): void
    {
        $data = self::DEFAULT_DATA;

        $this->save($name, $data);

        $this->crates[strtolower($name)] = new Crate($name, $data['drops'], $data['commands'], $data['floating-text'], $data['particle-id'], $data['particle-color']);
    }

    /**
     * @param string $name
     */
    public function removeCrate(string $name): void
    {
        $crate = $this->getCrateByName($name);

        if (Main::getInstance()->getPositionManager()->exists($name)) {
            Main::getInstance()->getPositionManager()->removePosition($name);
        }

        if ($crate !== null) {
            $crate->close();

            $this->data->remove($name);
            $this->data->save();

            unset($this->crates[strtolower($name)]);
        }
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
     * @param string $name
     * @param array $data
     */
    public function save(string $name, array $data)
    {
        $this->data->set($name, $data);

        $this->data->save();
    }

    public function closeAll(): void
    {
        foreach ($this->crates as $crate) {
            $crate->close();
        }
    }
}
