<?php

namespace UKMNorge\Rapporter\Framework\Word;

use UKMNorge\Rapporter\Framework\Config;

abstract class ConfigAware {
    static $config;

    /**
     * Opprett en ConfigAware-klasse
     *
     * @param Config $config
     */
    public function __construct( Config $config )
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
}