<?php

namespace UKMNorge\Rapporter;

use UKMnettverket;
use UKMNorge\Innslag\Samling;
use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;

class Lokalkontakter extends Rapport
{
    public $kategori_id = 'user_kontakt';
    public $ikon = 'dashicons-universal-access';
    public $navn = 'Lokalkontakter';
    public $beskrivelse = 'Kontaktinfo til alle dine lokalkontakter';

    /**
     * Hent alle fylke-områder current user kan administrere
     *
     * @return Array<Omrade>
     */
    public function getOmrader() {
        return UKMnettverket::getCurrentAdmin()->getOmrader('fylke');
    }

    /**
     * Hent hvilken template som skal benyttes
     *
     * @return String $template_id
     */
    public function getTemplate()
    {
        return 'Lokalkontakter/rapport.html.twig';
    }
}