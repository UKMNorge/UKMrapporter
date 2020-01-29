<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Rapporter\Framework\ConfigValue;
use UKMNorge\Rapporter\Program;

class Kunstkatalog extends Program
{
    public $kategori_id = 'program';
    public $ikon = 'dashicons-admin-customizer';
    public $navn = 'Kunstkatalog';
    public $beskrivelse = 'Alle innslag i utstillingen';
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
        #$this->getConfig()->add( new ConfigValue("vis_kategori_og_sjanger", "true") );
        $this->getConfig()->add( new ConfigValue("skjul_label_personer", "true"));
        
        return parent::getRenderData();
    }
}


/**
 * TODO
 * - Hvis krysset av for "vis personer", skjul innslagstittel fra navnet
 * - Skjul label "personer" i kunstkatalog
 */