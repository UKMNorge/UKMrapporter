<?php

namespace UKMNorge\Rapporter;

use Exception;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMrapporter;

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
     * Data til "tilpass rapporten"
     * 
     * @return Array
     */
    public function getCustomizerData()
    {
        try {
            return ['skjema' => $this->getArrangement()->getSkjema()];
        } catch (Exception $e) {
            if ($e->getCode() != 151002) {
                throw $e;
            }
        }
        return [];
    }

    /**
     * Hent hvilken template som skal benyttes
     *
     * @return String $template_id
     */
    public function getTemplate()
    {
        UKMrapporter::addViewData('skjema', $this->getArrangement()->getSkjema());
        return 'Skjema/rapport.html.twig';
    }
}
