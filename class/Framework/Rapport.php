<?php

namespace UKMNorge\Rapporter\Framework;

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Rapporter\Framework\Word\Formatter;

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
    public $krever_hendelse = false;
    public $har_word = false;
    public $har_excel = true;
    public $har_sms = true;
    public $har_epost = true;

    /**
     * Hent rapport-ID
     * 
     * @return String $id
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->id = basename(str_replace('UKMNorge\Rapporter\\', '', get_class($this)));
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

    /**
     * Støtter rapporten word-utgave?
     *
     * @return Bool
     */
    public function harWord()
    {
        return $this->supportWord();
    }

    /**
     * Støtter rapporten word-utgave?
     *
     * @see harWord()
     * @return Bool
     */
    public function supportWord()
    {
        return $this->har_word;
    }

    /**
     * Støtter rapporten excel-utgave?
     *
     * @return Bool
     */
    public function harExcel() {
        return $this->supportExcel();
    }

    /**
     * Støtter rapporten excel-utgave?
     *
     * @return Bool
     */
    public function supportExcel() {
        return $this->har_excel;
    }

    /**
     * Kan rapporten inneholde mobilnummer?
     *
     * @return Bool
     */
    public function harSms() {
        return $this->har_sms;
    }

    /**
     * Kan rapporten inneholde mobilnummer?
     *
     * @return Bool
     */
    public function supportSms() {
        return $this->har_sms;
    }

    /**
     * Kan rapporten inneholde e-postadresser?
     *
     * @return Bool
     */
    public function harEpost() {
        return $this->har_epost;
    }

    /**
     * Kan rapporten inneholde e-postadresser?
     *
     * @return Bool
     */
    public function supportEpost() {
        return $this->har_epost;
    }

    /**
     * Hent hvilken template som skal benyttes
     *
     * @return String $template_id
     */
    public function getTemplate()
    {
        return 'Components/renderRapport.html.twig';
    }

    /**
     * Konverter querystring til array
     *
     * @param String $string
     * @return Array $config
     */
    public function setConfigFromString($string)
    {
        $configData = [];
        parse_str($string, $configData);

        $this->config = new Config();
        $count = 0;
        foreach ($configData as $key => $val) {
            $count++;
            $this->config->add(new ConfigValue($key, $val));
        }
    }

    /**
     * Henter config-data for visning
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Henter render-data som brukes for å lage rapporten i 
     * html, excel eller word-format
     *
     * @return Gruppe $renderData
     */
    public function getRenderData()
    {
        new Gruppe('container', 'Alle innslag');
    }

    /**
     * Hent hvilket arrangement rapporten jobber med
     *
     * @return Arrangement $arrangement
     */
    public function getArrangement()
    {
        if (null == $this->arrangement) {
            $this->arrangement = new Arrangement(get_option('pl_id'));
        }
        return $this->arrangement;
    }

    /**
     * Lag og returner excel-filens URL
     * 
     * @return String url
     */
    public function getExcelFile()
    {
        $excel = new Excel(
            $this->getNavn() . ' oppdatert ' . date('d-m-Y') . ' kl '. date('Hi') . ' - ' . $this->getArrangement()->getNavn(),
            $this->getRenderDataInnslag(),
            $this->getConfig()
        );
        return $excel->writeToFile();
    }

    /**
     * Lag og returner word-filens URL
     * 
     * @return String url
     */
    public function getWordFile()
    {
        $word = new Word(
            $this->getNavn() . ' oppdatert ' . date('d-m-Y') . ' kl '. date('Hi') . ' - ' . $this->getArrangement()->getNavn(),
            $this->getRenderData(),
            $this->getConfig(),
            $this->getWordFormatter()
        );
        return $word->writeToFile();
    }

    /**
     * Hent standard wordFormatter
     *
     * Overskriv denne hvis rapporten krever egen wordFormatter 
     * 
     * @return Formatter
     */
    public function getWordFormatter()
    {
        return new Formatter($this->getConfig());
    }


    private function getRenderDataInnslag()
    {
        $renderData = $this->getRenderData();
        $this->_collected = [];
        if ($renderData->harGrupper()) {
            foreach ($renderData->getGrupper() as $gruppe) {
                if ($gruppe->harGrupper()) {
                    foreach ($gruppe->getGrupper() as $undergruppe) {
                        $this->_collectInnslag($undergruppe);
                    }
                } else {
                    $this->_collectInnslag($gruppe);
                }
            }
        } else {
            $this->_collectInnslag($renderData);
        }

        return $this->_collected;
    }

    private function _collectInnslag(Gruppe $gruppe)
    {
        foreach ($gruppe->getInnslag() as $innslag) {
            $this->_collected[$innslag->getId()] = $innslag;
        }
    }

    /**
     * Get the value of krever_hendelse
     */
    public function kreverHendelse()
    {
        return $this->krever_hendelse;
    }
}
