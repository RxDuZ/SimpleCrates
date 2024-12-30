<?php

namespace rxduz\crates\task;

use pocketmine\scheduler\Task;
use rxduz\crates\extension\Crate;
use rxduz\crates\Main;

class CrateUpdateTask extends Task
{

    public function __construct(private Main $plugin)
    {
        $this->setHandler($this->plugin->getScheduler()->scheduleRepeatingTask($this, 20));
    }

    public function onRun(): void
    {
        foreach ($this->plugin->getCrateManager()->getCrates() as $crate) {
            if ($crate instanceof Crate) {
                $crate->updatePreview();
            }
        }
    }
}
