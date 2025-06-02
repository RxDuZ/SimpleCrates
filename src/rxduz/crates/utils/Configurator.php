<?php

namespace rxduz\crates\utils;

use pocketmine\item\Item;
use RuntimeException;
use rxduz\crates\extension\Crate;

final class Configurator
{

    /**
     * @param string $name
     * @param Crate $crate
     * @param string $type
     * @param int $slot
     */
    public function __construct(
        private string $name,
        private Crate $crate,
        private string $type,
        private int $slot = 0
    ) {}

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
     * 
     * @throws RuntimeException If the drop not exists.
     */
    public function setItem(Item $item): void
    {
        $drops = $this->crate->getDrops();

        $slot = $this->slot;

        $drop = $drops[$slot] ?? null;

        if ($drop === null) {
            throw new RuntimeException('Drop ' . $slot . ' not found.');
        }

        $drop->setItem($item);

        $drops[$slot] = $drop;

        $this->crate->setDrops($drops);
    }

    /**
     * @param int $chance
     * 
     * @throws RuntimeException If the drop not exists.
     */
    public function setChance(int $chance): void
    {
        $drops = $this->crate->getDrops();

        $slot = $this->slot;

        $drop = $drops[$slot] ?? null;

        if ($drop === null) {
            throw new RuntimeException('Drop ' . $slot . ' not found.');
        }

        $drop->setChance($chance);

        $drops[$slot] = $drop;

        $this->crate->setDrops($drops);
    }

    /**
     * @param string[] $commands
     * 
     * @throws RuntimeException If the drop not exists.
     */
    public function setCommand(array $commands): void
    {
        $drops = $this->crate->getDrops();

        $slot = $this->slot;

        $drop = $drops[$slot] ?? null;

        if ($drop === null) {
            throw new RuntimeException('Drop ' . $slot . ' not found.');
        }

        $drop->setCommands($commands);

        $drops[$slot] = $drop;

        $this->crate->setDrops($drops);
    }
}
