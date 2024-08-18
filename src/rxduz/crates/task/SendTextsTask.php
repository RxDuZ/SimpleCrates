<?php

namespace rxduz\crates\task;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\World;
use rxduz\crates\extension\Crate;
use rxduz\crates\libs\texter\SendType;
use rxduz\crates\Main;

use function array_shift;

class SendTextsTask extends Task
{

    public const DELAY_TICKS = 5; // 0.25s
    public const TICKING_PERIOD = 2; // 0.1s

    /** @var Crate[] */
    private array $remain;

    public function __construct(
        private Player $target,
        World $sendTo,
        private SendType $type
    ) {
        $this->remain = Main::getInstance()->getCrateManager()->getCratesByWorld($sendTo);
    }

    public function onRun(): void
    {
        if (empty($this->remain) || !$this->target->isConnected()) {
            $this->onSuccess();
        } else {
            $crate = array_shift($this->remain);

            $floatingText = $crate->getFloatingTextHologram();

            if ($floatingText !== null) $floatingText->sendToPlayer($this->target, $this->type);
        }
    }

    private function onSuccess()
    {
        $this->getHandler()->cancel();
    }
}
