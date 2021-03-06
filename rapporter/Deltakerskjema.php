<?php

namespace UKMNorge\Rapporter;

use Exception;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMrapporter;

class Deltakerskjema extends Rapport
{
    public $kategori_id = 'personer';
    public $ikon = 'dashicons-universal-access';
    public $navn = 'Skjema fra deltakere';
    public $beskrivelse = 'Svar på de ekstra spørsmålene du la inn på arrangementssiden';
    public $har_excel = false;
    public $har_true = false;
    public $har_epost = true;

    /**
     * Data til "tilpass rapporten"
     * 
     * @return Array
     */
    public function getCustomizerData() {
        try {
            return ['skjema' => $this->getArrangement()->getDeltakerSkjema()];
        } catch( Exception $e ) {
            if( $e->getCode() != 151002 ) {
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
        UKMrapporter::addViewData('skjema', $this->getArrangement()->getDeltakerSkjema());
        return 'Skjema/rapport.html.twig';
    }
}