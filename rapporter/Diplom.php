<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Excel\Intoleranser as ExcelIntoleranser;
use UKMNorge\Rapporter\Framework\ConfigValue;
use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Rapporter\Word\FormatterDiplom;

class Diplom extends Rapport
{
    public $kategori_id = 'personer';
    public $ikon = 'dashicons-welcome-learn-more';
    public $navn = 'Diplomer';
    public $beskrivelse = 'Last ned ferdig wordfil klar for utskrift.';
    public $krever_hendelse = false;
    public $har_word = true;

    public function getRenderData()
    {
        $gruppe = new Gruppe('container', '');
        $gruppe->setVisOverskrift(false);

        foreach ($this->getArrangement()->getInnslag()->getAll() as $innslag) {
            foreach ($innslag->getPersoner()->getAll() as $person) {
                $gruppe->addPerson($person);
            }
        }
        return $gruppe;
    }

    /**
     * Hent spesifikk wordFormatter
     * 
     * @return WordFormatter
     */
    public function getWordFormatter() {
        $this->getConfig()->add(
            new ConfigValue(
                'arrangement_navn',
                $this->getArrangement()->getNavn()
            )    
        );

        return new FormatterDiplom( $this->getConfig() );
    }

    /**
     * Lag og returner excel-filens URL
     * 
     * @return String url
     */
    public function getExcelFile()
    {
        $excel = new ExcelIntoleranser(
            $this->getNavn() . ' oppdatert ' . date('d-m-Y') . ' kl ' . date('Hi') . ' - ' . $this->getArrangement()->getNavn(),
            $this->getRenderDataPersoner(),
            $this->getConfig()
        );
        return $excel->writeToFile();
    }
}
