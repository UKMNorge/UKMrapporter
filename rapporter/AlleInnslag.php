<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Framework\Meny;
use UKMNorge\Rapporter\Framework\Rapport;

class AlleInnslag extends Rapport {
    public $kategori_id = 'personer';
    public $ikon = 'dashicons-buddicons-buddypress-logo';
    public $navn = 'Alle innslag';
    public $beskrivelse = 'Informasjon om alle som er påmeldt arrangementet.';

    public function __construct()
    {
        $this->id = basename( str_replace('UKMNorge\Rapporter\\', '', get_class($this) ) );
    }

    /**
     * Overstyr hvilken fil som skal rendres basert på konfig
     *
     * @param Array $config
     * @return String $template
     */
    #public function filterRenderFile( Array $config ) {
    #    return 'NOPE'. $this->getId();
    #}
}