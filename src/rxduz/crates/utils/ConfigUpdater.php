<?php

namespace rxduz\crates\utils;

use pocketmine\utils\Config;
use rxduz\crates\Main;

class ConfigUpdater
{

    /**
     * @param Config $config
     * @param int $version
     */
    public static function checkUpdate(Config $config, int $version): void
    {
        if ((!$config->exists('CONFIG_VERSION')) or ($config->get('CONFIG_VERSION') !== $version)) {
            rename(Main::getInstance()->getDataFolder() . 'config.yml', Main::getInstance()->getDataFolder() . 'config_old.yml');

            Main::getInstance()->saveResource('/config.yml');

            Main::getInstance()->getLogger()->notice(Main::PREFIX . '(config.yml) The version does not match so it was updated.');

            $config->reload();
        }
    }
}
