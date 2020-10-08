<?php

namespace UKMNorge\Rapporter\Framework;

use Exception;
use UKMrapporter;
use Symfony\Component\Yaml\Yaml;

class Kategorier {
    public static $data = null;

    /**
     * Hent kategori med gitt ID
     *
     * @param String $id
     * @return Kategori
     */
    public static function getById( String $id) {
        static::getAll();
        if( !isset( static::$data[ $id ] ) ) {
            throw new Exception('UKMrapporter støtter ikke kategori '. $id );
        }
        return static::$data[ $id ];
    }

    /**
     * Last inn en kategori
     *
     * @return void
     */
    private static function _load() {
        $kategorier = Yaml::parse( file_get_contents( UKMrapporter::getPluginPath() .'rapporter/kategorier.yml') );
        foreach( $kategorier as $id => $kategori_data ) {
            $kategori = new Kategori( $id, $kategori_data );
            static::$data[ $kategori->getId() ] = $kategori;
        }
    }

    /**
     * Hent alle kategorier
     *
     * @return Array<Kategori>
     */
    public static function getAll() {
        if( static::$data == null ) {
            static::_load();
        }
        return static::$data;
    }
}