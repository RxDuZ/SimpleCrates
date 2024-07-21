<?php

namespace rxduz\crates\utils;

use pocketmine\item\Item;
use rxduz\crates\extension\Crate;

class Configurator {

    public function __construct(private string $name, private Crate $crate, private string $type, private int $slot = 0, private Item|null $item = null)
    {

    }

    /**
     * @return string
     */
    public function getUserName(): string {
        return $this->name;
    }

    /**
     * @return Crate
     */
    public function getCrate(): Crate {
        return $this->crate;
    }

    /**
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * @param int $chance
     */
    public function setChance(int $chance): void {
        $item = $this->crate->setChanceToItem($this->item, $chance);

        $this->item = $item;
    }

    /**
     * @param array $command
     */
    public function setCommand(array $command): void {
        $item = $this->crate->setCommandsToItem($this->item, $command);

        $this->item = $item;
    }

    /**
     * Save config
     */
    public function save(): void {
        if($this->item instanceof Item){
            $items = $this->crate->getItems();

            $items[$this->slot] = $this->item;

            $this->crate->setItems($items);
        }
    }

}

?>