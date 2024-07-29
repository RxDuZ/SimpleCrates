<?php

namespace rxduz\crates;

use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use rxduz\crates\command\CrateCommand;
use rxduz\crates\command\KeyAllCommand;
use rxduz\crates\command\KeyCommand;
use rxduz\crates\provider\YamlDataProvider;
use rxduz\crates\position\PositionManager;
use rxduz\crates\task\CrateUpdateTask;
use rxduz\crates\task\ParticleUpdateTask;
use rxduz\crates\translation\Translation;

class Main extends PluginBase
{

    use SingletonTrait;

    /** @var string */
    public const PREFIX = TextFormat::BOLD . TextFormat::DARK_GRAY . "(" . TextFormat::BLUE . "SimpleCrates" . TextFormat::DARK_GRAY . ")" . TextFormat::RESET . " ";

    /** @var CrateManager $crateManager */
    private CrateManager $crateManager;

    /** @var PositionManager $positionManager */
    private PositionManager $positionManager;

    /** @var YamlDataProvider $dataProvider */
    private YamlDataProvider $dataProvider;

    protected function onLoad(): void
    {
        self::setInstance($this);
    }

    protected function onEnable(): void
    {
        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }

        $this->saveDefaultConfig();

        $this->saveResource("/crates.yml");

        $this->saveResource("/messages.yml");

        $this->dataProvider = new YamlDataProvider($this);

        $this->positionManager = new PositionManager();

        $this->crateManager = new CrateManager();

        Translation::getInstance()->init();

        $this->getServer()->getCommandMap()->registerAll("SimpleCrates", [
            new KeyCommand(),
            new KeyAllCommand(),
            new CrateCommand()
        ]);

        new CrateUpdateTask($this);

        new ParticleUpdateTask($this);

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

        $this->getLogger()->info(self::PREFIX . TextFormat::GREEN . "plugin enabled successfully!");
    }

    protected function onDisable(): void
    {
        $this->getCrateManager()->closeAll();

        $this->getLogger()->info(self::PREFIX . TextFormat::RED . "plugin disabled!");
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

    /**
     * @return YamlDataProvider
     */
    public function getDataProvider(): YamlDataProvider
    {
        return $this->dataProvider;
    }
}
