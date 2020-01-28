<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Rapporter\Program;

class TekniskeProver extends Program
{
    public $kategori_id = 'program';
    public $ikon = 'dashicons-admin-settings';
    public $navn = 'Tekniske Prøver';
    public $beskrivelse = 'Kjøreplan for tekniske prøver';
    public $krever_hendelse = true;
}