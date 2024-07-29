<?php

namespace rxduz\crates\utils\particle;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\world\particle\Particle;

class CustomParticle implements Particle
{

    public function __construct(private int $particleId, private int $scale = 0)
    {
    }

    public function encode(Vector3 $pos): array
    {
        return [LevelEventPacket::standardParticle($this->particleId, $this->scale, $pos)];
    }
}
