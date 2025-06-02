<?php

namespace rxduz\crates\extension;

use pocketmine\item\Item;

final class Drop
{
    /**
     * @param Item $item
     * @param string $type
     * @param string[] $commands
     * @param int $chance
     */
    public function __construct(
        private Item $item,
        private string $type = 'item',
        private array $commands = [],
        private int $chance = 10
    ) {}

    /**
     * @return Item
     */
    public function getItem(): Item
    {
        return $this->item;
    }

    /**
     * @param Item $item
     */
    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @param string[] $commnads
     */
    public function setCommands(array $commands): void
    {
        $this->commands = $commands;
    }

    /**
     * @return int
     */
    public function getChance(): int
    {
        return $this->chance;
    }

    /**
     * @param int $chance
     */
    public function setChance(int $chance): void
    {
        $this->chance = $chance;
    }
}
