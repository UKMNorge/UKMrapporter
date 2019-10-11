<?php

namespace UKMNorge\Rapporter\Framework;

use UKMNorge\Arrangement\Arrangement;

require_once('UKM/Autoloader.php');


abstract class Rapport
{
    public $id;
    public $navn;
    public $kategori_id;
    public $beskrivelse;
    public $ikon;
    public $config;
    public $arrangement;

    /**
     * Hent rapport-ID
     * 
     * @return String $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Hent rapport-navn
     * 
     * @return String $navn
     */
    public function getNavn()
    {
        return $this->navn;
    }

    /**
     * Hent rapportens kategori
     * 
     * @return String $kategori
     */
    public function getKategori()
    {
        return Kategorier::getById($this->kategori_id);
    }

    /**
     * Hent beskrivelsen
     * 
     * @return String $beskrivelse
     */
    public function getBeskrivelse()
    {
        return $this->beskrivelse;
    }

    /**
     * Hent rapportens ikon
     * 
     * @return String $dashicon-ID
     */
    public function getIkon()
    {
        return $this->ikon;
    }

    public function supportExcel()
    {
        return method_exists($this, 'renderExcel');
    }
    public function supportWord()
    {
        return method_exists($this, 'renderWord');
    }

    /**
     * Hent hvilken template som skal benyttes
     *
     * @return String $template_id
     */
    public function getTemplate() {
        return $this->getId().'/rapport';
    }

    /**
     * Konverter querystring til array
     *
     * @param String $string
     * @return Array $config
     */
    public function setConfigFromString( $string ) {
        parse_str( $string, $configData );

        $this->config = new Config();
        $count = 0;
        foreach( $configData as $key => $val ) {
            $count++;
            $this->config->add( new ConfigValue($key, $val) );
        }
    }

    /**
     * Henter config-data for visning
     *
     * @return Config
     */
    public function getConfig() {
        return $this->config;
    }

    public function getRenderData( $responseData ) {
        return $responseData;
    }

    public function getArrangement() {
        if( null == $this->arrangement ) {
            $this->arrangement = new Arrangement( get_option('pl_id') );
        }
        return $this->arrangement;
    }
}
