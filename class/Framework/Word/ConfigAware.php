<?php

namespace UKMNorge\Rapporter\Framework\Word;

use UKMNorge\Rapporter\Framework\Config;

abstract class ConfigAware
{
    static $config;

    /**
     * Opprett en ConfigAware-klasse
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        static::$config = $config;
    }

    /**
     * Hent config for rapport-rendring
     *
     * @return Config
     */
    public static function getConfig()
    {
        return static::$config;
    }

    /**
     * Skal det vises? Proxy for static::getConfig()->show()
     * 
     * @param String $key
     * @return Bool
     */
    public static function show(String $key)
    {
        return static::getConfig()->show($key);
    }
}
