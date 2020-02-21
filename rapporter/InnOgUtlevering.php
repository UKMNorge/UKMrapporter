<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Rapporter\Framework\ConfigValue;
use UKMNorge\Rapporter\Program;

class InnOgUtlevering extends TypeInnslag
{
    public $kategori_id = 'personer';
    public $ikon = 'dashicons-clipboard';
    public $navn = 'Inn og utlevering';
    public $beskrivelse = 'Innsjekk-lister for kunstverk og filmer';
    public $krever_hendelse = true;
    public $har_word = false;


    /**
     * Hent render-data for rapporten
     *
     * @return Gruppe
     */
    public function getRenderData()
    {
        $this->getConfig()->add( new ConfigValue("vis_titler", "false") );
        $this->getConfig()->add( new ConfigValue("skjul_label_personer", "true"));
        
        return parent::getRenderData();
    }

    /**
     * Hent excel-fil (og få med arket titler)
     *
     * @inheritdoc
     */
    public function getExcelFile()
    {
        $this->getConfig()->get('vis_titler')->setValue(true);
        return parent::getExcelFile();
    }
}