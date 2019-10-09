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
}