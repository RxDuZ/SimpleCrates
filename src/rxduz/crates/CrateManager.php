<?php

namespace rxduz\crates;

use Exception;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;
use rxduz\crates\extension\Crate;
use rxduz\crates\extension\Drop;
use rxduz\crates\Main;
use rxduz\crates\utils\Configurator;
use rxduz\crates\utils\Utils;

final class CrateManager
{

    /** @var array */
    public const DEFAULT_DATA = [
        'drops' => [],
        'commands' => [],
        'floating-text' => '',
        'particle-enabled' => true,
        'particle-id' => -1,
        'particle-color' => '0:255:0',
        'opening-animation' => 'none'
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

    /** @var string */
    public const CONFIGURATOR_CRATE_OPENING_ANIMATION = 'opening_animation';

    /** @var Config */
    private Config $data;

    /** @var array<string, Configurator>*/
    private array $setters = [];

    /** @var string[] */
    private array $skipAnimation = [];

    /** @var array<string, Crate> */
    private array $crates = [];

    public function __construct()
    {
        $this->data = new Config(Main::getInstance()->getDataFolder() . '/crates.yml', Config::YAML);

        foreach ($this->data->getAll() as $name => $value) {
            $drops = [];

            foreach ($value['drops'] ?? [] as $slot => $drop) {
                $chance = $drop['chance'] ?? 10;

                $commands = $drop['commands'] ?? [];

                $drops[intval($slot)] = new Drop(
                    Utils::legacyStringJsonDeserialize($drop['item']),
                    (empty($commands) ? 'item' : 'command'),
                    $commands,
                    $chance
                );
            }

            $this->crates[strtolower($name)] = new Crate(
                $name,
                $drops,
                $value['commands'] ?? [],
                $value['floating-text'] ?? '',
                $value['particle-enabled'] ?? true,
                $value['particle-id'] ?? -1,
                $value['particle-color'] ?? '0:255:0',
                $value['opening-animation'] ?? 'none'
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
     * 
     * @return bool
     */
    public function exists(string $name): bool
    {
        return $this->getCrateByName($name) !== null;
    }

    /**
     * @param Position $position
     * 
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
     * 
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

        $this->crates[strtolower($name)] = new Crate($name, $data['drops'], $data['commands'], $data['floating-text'], $data['particle-enabled'], $data['particle-id'], $data['particle-color'], $data['opening-animation']);
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
     * 
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
     * 
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
        if (isset($this->setters[$name])) unset($this->setters[$name]);
    }

    /**
     * @param string $name
     * 
     * @return bool
     */
    public function inSkipAnimation(string $name): bool
    {
        return in_array($name, $this->skipAnimation, true);
    }

    /**
     * @param string $name
     */
    public function setSkipAnimation(string $name): void
    {
        $this->skipAnimation[] = $name;
    }

    /**
     * @param string $name
     */
    public function removeSkipAnimation(string $name): void
    {
        $key = array_search($name, $this->skipAnimation);

        if ($key !== false) unset($this->skipAnimation[$key]);
    }

    /**
     * @param string $name
     * @param array $data
     */
    public function save(string $name, array $data): void
    {
        $this->data->set($name, $data);

        try {
            $this->data->save();
        } catch (Exception $e) {
            Main::getInstance()->getLogger()->error('Failed to save crate ' . $name . ': ' . $e->getMessage());
        }
    }

    public function closeAll(): void
    {
        foreach ($this->crates as $crate) {
            $crate->close();
        }
    }
}
