<?php

namespace rxduz\crates\utils;

use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\Server;

class Utils {

	/**
	 * @param Item $item
	 * @return array
	 */
	public static function jsonSerialize(Item $item): array 
	{
		$itemData = [];

		$itemData["nbt_b64"] = base64_encode((new LittleEndianNbtSerializer())->write(new TreeRoot($item->nbtSerialize())));
		
		return $itemData;
	}

	/**
	 * @param array $data
	 * @return Item
	 */
	public static function legacyStringJsonDeserialize(array $data): Item 
	{
		$buffer = base64_decode($data["nbt_b64"]);

		$item = (new LittleEndianNbtSerializer())->read($buffer);

		return Item::nbtDeserialize($item->mustGetCompoundTag());
	}

	/**
	 * @param Player $player
	 * @param string $soundName
	 * @param float $volume
	 * @param float $pitch
	 */
    public static function playSound(Player $player, string $soundName, float $volume = 1.0, float $pitch = 1.0): void 
	{
		$pk = PlaySoundPacket::create(
			$soundName,
			$player->getLocation()->asVector3()->getX(),
			$player->getLocation()->asVector3()->getY(),
			$player->getLocation()->asVector3()->getZ(),
			$volume,
			$pitch
		);

		$player->getNetworkSession()->sendDataPacket($pk);
	}

	/**
	 * @param string $crateName
	 */
	public static function clearItems(string $crateName): void 
	{
        foreach(Server::getInstance()->getWorldManager()->getWorlds() as $world){
            foreach($world->getEntities() as $entity){
                if($entity instanceof ItemEntity){
                    if($entity->getItem()->getNamedTag()->getTag("CrateItem") !== null and $entity->getItem()->getNamedTag()->getString("CrateItem") === $crateName){
                        $entity->flagForDespawn();
                    }
                }
            }
        }
    }

}

?>