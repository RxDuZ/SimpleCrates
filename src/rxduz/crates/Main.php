<?php

namespace rxduz\crates;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\PacketHooker;
use JackMD\ConfigUpdater\ConfigUpdater;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use rxduz\crates\command\CrateCommand;
use rxduz\crates\command\KeyAllCommand;
use rxduz\crates\command\KeyCommand;
use rxduz\crates\extension\animations\OpeningAnimationUtils;
use rxduz\crates\position\PositionManager;
use rxduz\crates\translation\Translation;

class Main extends PluginBase
{

    use SingletonTrait {
        setInstance as private;
        reset as private;
    }

    /** @var string */
    public const PREFIX = TextFormat::BOLD . TextFormat::DARK_GRAY . '(' . TextFormat::BLUE . 'SimpleCrates' . TextFormat::DARK_GRAY . ')' . TextFormat::RESET . ' ';

    /** @var int */
    public const CONFIG_VERSION = 2;

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
                'ConfigUpdater' => ConfigUpdater::class
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

        $this->saveResource('/templates.yml');

        $this->positionManager = new PositionManager();

        $this->crateManager = new CrateManager();

        ConfigUpdater::checkUpdate($this, $this->getConfig(), 'CONFIG_VERSION', self::CONFIG_VERSION);

        Translation::getInstance()->load($this);

        OpeningAnimationUtils::getInstance()->load($this);

        $this->getServer()->getCommandMap()->registerAll('SimpleCrates', [
            new KeyCommand($this),
            new KeyAllCommand($this),
            new CrateCommand($this)
        ]);

        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            foreach ($this->getCrateManager()->getCrates() as $crate) {
                $crate->updatePreview();
            }
        }), 20);

        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            foreach ($this->getCrateManager()->getCrates() as $crate) {
                $crate->updateParticles();
            }
        }), 1);

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
