<?php

namespace rxduz\crates\utils;

use pocketmine\item\Item;
use rxduz\crates\extension\Crate;

class Configurator
{

    public function __construct(private string $name, private Crate $crate, private string $type, private int $slot = 0) {}

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->name;
    }

    /**
     * @return Crate
     */
    public function getCrate(): Crate
    {
        return $this->crate;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param Item $item
     */
    public function setItem(Item $item): void
    {
        $drops = $this->crate->getDrops();

        if (array_key_exists($this->slot, $drops)) {
            $drops[$this->slot]['item'] = $item;
        }

        $this->crate->setDrops($drops);
    }

    /**
     * @param int $chance
     */
    public function setChance(int $chance): void
    {
        $drops = $this->crate->getDrops();

        if (array_key_exists($this->slot, $drops)) {
            $drops[$this->slot]['chance'] = $chance;
        }

        $this->crate->setDrops($drops);
    }

    /**
     * @param array $command
     */
    public function setCommand(array $command): void
    {
        $drops = $this->crate->getDrops();

        if (array_key_exists($this->slot, $drops)) {
            $drops[$this->slot]['commands'] = $command;
        }

        $this->crate->setDrops($drops);
    }
}
