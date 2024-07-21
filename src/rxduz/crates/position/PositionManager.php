<?php

namespace rxduz\crates\position;

use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use rxduz\crates\Main;

class PositionManager {

    /** @var Config $data */
    private Config $data;

    /** @var Position[] */
    private array $positions = [];

    public function __construct(){
        $this->data = Main::getInstance()->getDataProvider()->getConfiguration("/positions");

        foreach($this->data->getAll() as $name => $value){
            $worldName = $value["world"];

            $pos = $value["position"];

            $worldManager = Server::getInstance()->getWorldManager();

            if(!$worldManager->loadWorld($worldName)){
                Server::getInstance()->getLogger()->info(Main::PREFIX . TextFormat::MINECOIN_GOLD . "The world " . $worldName . " could not be loaded therefore the position " . $name . " is ignored");

                continue;
            }

            $this->positions[$name] = new Position($pos["X"], $pos["Y"], $pos["Z"], Server::getInstance()->getWorldManager()->getWorldByName($worldName));
        }
    }

    /**
     * @return Position[]
     */
    public function getPositions(): array {
        return $this->positions;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function exists(string $name): bool {
        return isset($this->positions[$name]);
    }

    /**
     * @param string $name
     * @return Position|null
     */
    public function getPositionByName(string $name): Position|null {
        return $this->positions[$name] ?? null;
    }

    /**
     * @param string $name
     * @param Position $position
     */
    public function createPosition(string $name, Position $position): void {
        $data = [
            "world" => $position->getWorld()->getFolderName(),
            "position" => ["X" => $position->getX(), "Y" => $position->getY(), "Z" => $position->getZ()]
        ];

        $this->data->set($name, $data);

        $this->data->save();

        $this->positions[$name] = $position;
    }

    /**
     * @param string $name
     */
    public function removePosition(string $name): void {
        $this->data->remove($name);

        $this->data->save();

        if(isset($this->positions[$name])) unset($this->positions[$name]);
    }

}
?>