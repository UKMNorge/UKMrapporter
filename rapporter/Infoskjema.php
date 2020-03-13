<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Innslag\Samling;
use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;

class Infoskjema extends Rapport
{
    public $kategori_id = 'videresending';
    public $ikon = 'dashicons-forms';
    public $navn = 'Spørreskjema';
    public $beskrivelse = 'Svar på spørreskjema til de som videresender';
    public $har_excel = false;
    public $har_sms = false;
    public $har_epost = false;


    /**
     * Hent hvilken template som skal benyttes
     *
     * @return String $template_id
     */
    public function getTemplate()
    {
        return 'Infoskjema/rapport.html.twig';
    }
}