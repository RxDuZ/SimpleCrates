<?php

namespace rxduz\crates\utils\particle;

use pocketmine\color\Color;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\ParticleIds;
use pocketmine\world\particle\Particle;

class DusterParticle implements Particle
{

	public function __construct(private Color $color)
	{
	}

	/**
	 * @param Vector3 $pos
	 * @return array
	 */
	public function encode(Vector3 $pos): array
	{
		return [LevelEventPacket::standardParticle(ParticleIds::MOB_SPELL_INSTANTANEOUS, $this->color->toARGB(), $pos)];
	}
}
