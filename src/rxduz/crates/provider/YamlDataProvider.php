<?php

namespace rxduz\crates\provider;

use pocketmine\utils\Config;
use rxduz\crates\Main;

class YamlDataProvider {
	
	/** @var array $$pluginConfig */
	private array $pluginConfig = [];

	public function __construct(private Main $plugin){
		$this->pluginConfig = $this->plugin->getConfig()->getAll();
	}
	
	/**
	 * @return array
	 */
	public function getPluginConfig(): array {
		return $this->pluginConfig;
	}
	
	/**
	 * @param string $location
	 * @return Config
	 */
	public function getConfiguration(string $location): Config {
		return new Config($this->plugin->getDataFolder() . $location . ".yml", Config::YAML);
	}
}

?>