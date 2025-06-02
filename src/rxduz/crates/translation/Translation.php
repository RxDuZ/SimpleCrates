<?php

namespace rxduz\crates\translation;

use JackMD\ConfigUpdater\ConfigUpdater;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use rxduz\crates\Main;

final class Translation
{

	use SingletonTrait {
		setInstance as private;
		reset as private;
	}

	/** @var int */
	public const MESSAGES_VERSION = 2;

	/** @var Config|null */
	private Config|null $messages = null;

	/**
	 * @param Main $plugin
	 */
	public function load(Main $plugin): void
	{
		$this->messages = new Config($plugin->getDataFolder() . '/messages.yml', Config::YAML);

		ConfigUpdater::checkUpdate($plugin, $this->messages, 'MESSAGES_VERSION', self::MESSAGES_VERSION);
	}

	/**
	 * @param string $key
	 * @param array $tags
	 * 
	 * @return string
	 */
	public function getMessage(string $key, array $tags = []): string
	{
		$message = $this->messages->get($key, 'Message \'' . $key . '\'does not exist.');

		foreach ($tags as $tag => $value) {
			$message = str_replace($tag, strval($value), $message);
		}

		return TextFormat::colorize($message);
	}
}
