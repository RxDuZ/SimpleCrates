<?php

namespace rxduz\crates\translation;

use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use rxduz\crates\Main;

class Translation
{

	use SingletonTrait;

	/** @var int */
	public const VERSION = 1.2;

	/** @var string */
	public const EMPTY_MESSAGE = TextFormat::RED . "This message does not exist or was deleted";

	/** @var Config|null $data */
	private Config|null $data = null;

	/** @var array */
	private array $messages = [];

	public function init()
	{
		$this->data = new Config(Main::getInstance()->getDataFolder() . "/messages.yml", Config::YAML);

		if ((!$this->data->exists("MESSAGES_VERSION")) or ($this->data->get("MESSAGES_VERSION") !== self::VERSION)) {
			rename(Main::getInstance()->getDataFolder() . "messages.yml", Main::getInstance()->getDataFolder() . "messages_old.yml");

			Main::getInstance()->saveResource("/messages.yml");

			Main::getInstance()->getLogger()->notice(Main::PREFIX . "(messages.yml) The version does not match so it was updated.");

			$this->data = new Config(Main::getInstance()->getDataFolder() . "/messages.yml", Config::YAML);
		}

		$this->messages = $this->data->getAll();
	}

	public function getData(): Config|null
	{
		return $this->data;
	}

	/**
	 * @param string $key
	 * @param array $replace
	 * @return string
	 */
	public function getMessage(string $key, array $replace = []): string
	{
		$message = $this->messages[$key] ?? self::EMPTY_MESSAGE;

		foreach ($replace as $k => $v) {
			$message = str_replace($k, strval($v), $message);
		}

		return TextFormat::colorize($message);
	}
}
