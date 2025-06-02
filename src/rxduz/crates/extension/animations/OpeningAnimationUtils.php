<?php

namespace rxduz\crates\extension\animations;

use InvalidArgumentException;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use RuntimeException;
use rxduz\crates\extension\Crate;
use rxduz\crates\Main;
use rxduz\crates\task\MysteryAnimationTask;
use rxduz\crates\task\RouletteAnimationTask;
use rxduz\crates\task\SpinningAnimationTask;

final class OpeningAnimationUtils
{

    use SingletonTrait {
        setInstance as private;
        reset as private;
    }

    /** @var string */
    public const SPINNING = 'spinning';

    /** @var string */
    public const ROULETTE = 'roulette';

    /** @var string */
    public const MYSTERY = 'mystery';

    /** @var Config|null */
    private Config|null $templatesConfig = null;

    /**
     * @param Main $plugin
     */
    public function load(Main $plugin): void
    {
        $this->templatesConfig = new Config($plugin->getDataFolder() . '/templates.yml', Config::YAML);
    }

    /**
     * @return string[]
     */
    public function getAnimationsLocal(): array
    {
        return [self::SPINNING, self::ROULETTE, self::MYSTERY];
    }

    /**
     * @param string $animationId
     * 
     * @return bool
     */
    public function exists(string $animationId): bool
    {
        return in_array($animationId, $this->getAnimationsLocal());
    }

    /**
     * @param string $animationId
     * 
     * @return array
     * 
     * @throws RuntimeException If config not initialized or if the data is invalid.
     */
    public function getTemplateData(string $animationId): array
    {
        $templatesConfig = $this->templatesConfig;

        if ($templatesConfig === null) {
            throw new RuntimeException('Templates config is not initialized.');
        }

        $templateData = $templatesConfig->get($animationId);

        if (!is_array($templateData)) {
            throw new RuntimeException('Invalid template data.');
        }

        return $templateData;
    }

    /**
     * @param string $animationId
     * @param Crate $crate
     * @param Player $player
     * 
     * @throws InvalidArgumentException If the animation not exists or if the data is invalid.
     */
    public function sendAnimationTask(string $animationId, Crate $crate, Player $player): void
    {
        if (!$this->exists($animationId)) {
            throw new RuntimeException('Animation ' . $animationId . ' not exists.');
        }

        $templateData = $this->getTemplateData($animationId);

        if (!isset($templateData['speed']) or !is_int($templateData['speed'])) {
            throw new InvalidArgumentException('Invalid speed data.');
        }

        if (!isset($templateData['duration']) or !is_int($templateData['duration'])) {
            throw new InvalidArgumentException('Invalid duration data.');
        }

        $background = $templateData['background'];

        if (!is_array($background) or !isset($background['block']) or !isset($background['type'])) {
            throw new InvalidArgumentException('Invalid background data');
        }

        $task = match (strtolower($animationId)) {
            self::SPINNING => new SpinningAnimationTask($crate, $player, $templateData),
            self::ROULETTE => new RouletteAnimationTask($crate, $player, $templateData),
            self::MYSTERY => new MysteryAnimationTask($crate, $player, $templateData)
        };

        $crate->setOpen(true);

        Main::getInstance()->getScheduler()->scheduleRepeatingTask($task, 1);
    }
}
