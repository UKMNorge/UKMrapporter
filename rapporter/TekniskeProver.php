<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Program;
use UKMNorge\Rapporter\Word\FormatterTekniskeProver;

class TekniskeProver extends Program
{
    public $kategori_id = 'program';
    public $ikon = 'dashicons-admin-settings';
    public $navn = 'Tekniske Prøver';
    public $beskrivelse = 'Kjøreplan for tekniske prøver';
    public $krever_hendelse = true;

    /**
     * Hent spesifikk wordFormatter
     * 
     * @return FormatterTekniskeProver
     */
    public function getWordFormatter()
    {
        return new FormatterTekniskeProver($this->getConfig());
    }
}
