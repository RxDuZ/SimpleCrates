<?php

namespace rxduz\crates\libs\texter;

use pocketmine\player\Player;
use pocketmine\world\World;

interface Sendable
{

    public function sendToPlayer(Player $player, SendType $type): void;

    public function sendToPlayers(array $players, SendType $type): void;

    public function sendToWorld(World $world, SendType $type): void;
}
