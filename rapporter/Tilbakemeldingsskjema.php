<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Rapporter\Framework\ConfigValue;
use UKMNorge\Rapporter\Program;


class Tilbakemeldingsskjema extends Program
{
    public $kategori_id = 'program';
    public $ikon = 'dashicons-editor-ol';
    public $navn = 'Tilbakemeldingsskjema';
    public $beskrivelse = 'Noteringsskjema for fagpanelet';
    public $krever_hendelse = true;

    /**
     * Hent render-data for rapporten
     *
     * @return Gruppe
     */
    public function getRenderData()
    {
        $this->getConfig()->add( new ConfigValue("vis_titler", "true") );
        $this->getConfig()->add( new ConfigValue("vis_notatfelt_jury", "true") );
        $this->getConfig()->add( new ConfigValue("notatfelt_visning", "gigantisk") );
        
        return parent::getRenderData();
    }
}