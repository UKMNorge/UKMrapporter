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

    /**
     * Hent render-data for rapporten
     *
     * @return Gruppe
     */
    public function getRenderData()
    {
        $this->getConfig()->add( new ConfigValue("vis_titler", "true") );
        $this->getConfig()->add( new ConfigValue("vis_kategori_og_sjanger", "true") );
        
        return parent::getRenderData();
    }
}