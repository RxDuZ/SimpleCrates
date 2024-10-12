<?php

namespace rxduz\crates;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\PacketHooker;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use rxduz\crates\command\CrateCommand;
use rxduz\crates\command\KeyAllCommand;
use rxduz\crates\command\KeyCommand;
use rxduz\crates\migrator\VersionMigrator;
use rxduz\crates\position\PositionManager;
use rxduz\crates\task\CrateUpdateTask;
use rxduz\crates\task\ParticleUpdateTask;
use rxduz\crates\translation\Translation;
use rxduz\crates\utils\ConfigUpdater;

class Main extends PluginBase
{

    use SingletonTrait;

    /** @var string */
    public const PREFIX = TextFormat::BOLD . TextFormat::DARK_GRAY . '(' . TextFormat::BLUE . 'SimpleCrates' . TextFormat::DARK_GRAY . ')' . TextFormat::RESET . ' ';

    /** @var int */
    public const CONFIG_VERSION = 1;

    /** @var CrateManager $crateManager */
    private CrateManager $crateManager;

    /** @var PositionManager $positionManager */
    private PositionManager $positionManager;

    protected function onLoad(): void
    {
        self::setInstance($this);
    }

    protected function onEnable(): void
    {
        foreach (
            [
                'Commando' => BaseCommand::class,
                'InvMenu' => InvMenuHandler::class,
            ] as $virion => $class
        ) {
            if (!class_exists($class)) {
                $this->getLogger()->error($virion . ' virion not found. Please download the needed virions and try again.');

                $this->getServer()->getPluginManager()->disablePlugin($this);

                return;
            }
        }

        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }

        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }

        $this->saveDefaultConfig();

        $this->saveResource('/crates.yml');

        $this->saveResource('/messages.yml');

        $this->positionManager = new PositionManager();

        $this->crateManager = new CrateManager();

        ConfigUpdater::checkUpdate($this->getConfig(), self::CONFIG_VERSION);

        Translation::getInstance()->init();

        $this->getServer()->getCommandMap()->registerAll('SimpleCrates', [
            new KeyCommand($this),
            new KeyAllCommand($this),
            new CrateCommand($this)
        ]);

        new CrateUpdateTask($this);

        new ParticleUpdateTask($this);

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

        $this->getLogger()->info(self::PREFIX . TextFormat::GREEN . 'plugin enabled successfully made by iRxDuZ.');
    }

    protected function onDisable(): void
    {
        $this->getCrateManager()->closeAll();

        $this->getLogger()->info(self::PREFIX . TextFormat::RED . 'plugin disabled.');
    }

    /**
     * @return CrateManager
     */
    public function getCrateManager(): CrateManager
    {
        return $this->crateManager;
    }

    /**
     * @return PositionManager
     */
    public function getPositionManager(): PositionManager
    {
        return $this->positionManager;
    }
}
