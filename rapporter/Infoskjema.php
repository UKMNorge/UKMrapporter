<?php

namespace UKMNorge\Rapporter;

use Exception;
use UKMNorge\Arrangement\Skjema\Skjema;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Arrangement\Skjema\SvarSett;
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
        $arrangement = $this->getArrangement();
        $svarsett = [];
        $alleArrangementer = [];

        // Hent alle arrangementer som videresender 
        foreach($arrangement->getVideresending()->getAvsendere() as $arrangAvsender) {
            $fraArrangement = $arrangAvsender->getArrangement();
            try{
                $skjemaFra = $fraArrangement->getSkjema();
                $svarsett[] = SvarSett::getPlaceholder('arrangement', $fraArrangement->getId(), $skjemaFra->getId());
                $alleArrangementer[] = $fraArrangement;
            }catch(Exception $e) {
                if($e->getCode() == 151002) {
                    // Skjemma finnes ikke, fortsett videre fordi det er ikke nødvendigvis at alle arrangementer har sendt skjema
                    continue;
                }
            }
        }

        $skjema = $arrangement->getSkjema();

        UKMrapporter::addViewData('skjema', $skjema);
        UKMrapporter::addViewData('svarsett', $svarsett);
        UKMrapporter::addViewData('alleArrangementer', $alleArrangementer);

        return 'Skjema/rapport.html.twig';
    }
}
