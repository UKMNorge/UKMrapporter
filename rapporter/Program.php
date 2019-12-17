<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;

class Program extends Rapport
{
    public $kategori_id = 'program';
    public $ikon = 'dashicons-editor-ol';
    public $navn = 'Program';
    public $beskrivelse = 'Program for dine hendelser';

}