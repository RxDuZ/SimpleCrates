<?php

namespace rxduz\crates\translation;

use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use rxduz\crates\Main;

class Translation {

	use SingletonTrait;

	/** @var string */
	public const EMPTY_MESSAGE = TextFormat::RED . "This message does not exist or was deleted";
	
	/** @var array */
	private array $messages = [];
	
	public function init(){
		$file = new Config(Main::getInstance()->getDataFolder() . "/messages.yml", Config::YAML);

		$this->messages = $file->getAll();
	}
	
	/**
	 * @param string $key
	 * @param array $replace
	 * @return string
	 */
	public function getMessage(string $key, array $replace = []): string {
		$message = $this->messages[$key] ?? self::EMPTY_MESSAGE;

		foreach($replace as $k => $v){
			$message = str_replace($k, strval($v), $message);
		}

		return TextFormat::colorize($message);
	}
	
}

?>