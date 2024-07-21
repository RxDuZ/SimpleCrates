<?php

namespace rxduz\crates\task;

use pocketmine\scheduler\Task;
use rxduz\crates\extension\Crate;
use rxduz\crates\Main;

class ParticleUpdateTask extends Task {

    public function __construct(private Main $plugin){
        $this->setHandler($this->plugin->getScheduler()->scheduleRepeatingTask($this, 1));
    }

    public function onRun(): void
    {
        foreach($this->plugin->getCrateManager()->getCrates() as $crate){
            if($crate instanceof Crate){
                $crate->updateParticles();
            }
        }
    }

}

?>