<?php

namespace rxduz\crates\task;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\particle\LavaParticle;
use rxduz\crates\extension\Crate;
use rxduz\crates\translation\Translation;
use rxduz\crates\utils\Utils;

class OpenAnimationTask extends Task
{

    public function __construct(private Crate $crate, private Player $player, private int $time, private array $drop) {}

    public function onRun(): void
    {
        if (!$this->player->isConnected()) {
            $this->crate->setOpen(false);

            $this->getHandler()->cancel();

            return;
        }

        if ($this->time === 0) {
            $drop = $this->drop;

            $item = clone $drop["item"];

            if ($drop["type"] === "item") {
                if ($this->player->getInventory()->canAddItem($item)) {
                    $this->player->getInventory()->addItem($item);
                } else {
                    $this->player->dropItem($item);
                }
            }

            foreach ($drop["commands"] as $dropCommand) {
                $this->player->getServer()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), str_replace("{PLAYER}", '"' . $this->player->getName() . '"', $dropCommand));
            }

            foreach ($this->crate->getCommands() as $crateCommand) {
                $this->player->getServer()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), str_replace("{PLAYER}", '"' . $this->player->getName() . '"', $crateCommand));
            }

            $this->player->sendTip(Translation::getInstance()->getMessage("CRATE_OPEN_REWARD", ["{CRATE}" => $this->crate->getName(), "{REWARD}" => $item->getName()]));

            Utils::playSound($this->player, "random.explode", 20);

            $cratePosition = $this->crate->getPosition();

            $x = $cratePosition->getX() + 0.5;

            $y = $cratePosition->getY() + 1;

            $z = $cratePosition->getZ() + 0.5;

            $radius = 1;

            for ($i = 0; $i < 20; $i++) {
                $cx = $x + ($radius * cos($i));

                $cz = $z + ($radius * sin($i));

                $position = new Vector3($cx, $y, $cz);

                $cratePosition->getWorld()->addParticle($position, new LavaParticle(), [$this->player]);
            }

            $this->crate->setOpen(false);

            $this->getHandler()->cancel();
        } else {
            $this->player->sendTitle(Translation::getInstance()->getMessage("CRATE_ANIMATION_TITLE", ["{TIME}" => $this->time]), Translation::getInstance()->getMessage("CRATE_ANIMATION_SUBTITLE", ["{TIME}" => $this->time]), 20, 20, 20);

            Utils::playSound($this->player, "note.pling", 20, $this->time);
        }

        $this->time--;
    }
}
