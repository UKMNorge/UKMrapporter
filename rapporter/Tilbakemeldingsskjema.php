<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\ConfigValue;
use UKMNorge\Rapporter\Program;
use UKMNorge\Rapporter\Word\FormatterTilbakemeldingsskjema;

class Tilbakemeldingsskjema extends Program
{
    public $kategori_id = 'program';
    public $ikon = 'dashicons-welcome-write-blog';
    public $navn = 'Tilbakemeldingsskjema';
    public $beskrivelse = 'Noteringsskjema for fagpanelet';
    public $krever_hendelse = true;
    public $har_word = true;

    /**
     * Hent render-data for rapporten
     *
     * @return Gruppe
     */
    public function getRenderData()
    {
        $this->getConfig()->add( new ConfigValue("vis_titler", "true") );
        $this->getConfig()->add( new ConfigValue("vis_notatfelt_jury", "true") );
        $this->getConfig()->add( new ConfigValue("notatfelt_visning", "stor") );
        $this->getConfig()->add( new ConfigValue("vis_vurderingsfelt", "true") );
        
        return parent::getRenderData();
    }

    /**
     * Hent spesifikk wordFormatter
     * 
     * @return WordFormatter
     */
    public function getWordFormatter() {
        return new FormatterTilbakemeldingsskjema( $this->getConfig() );
    }
}