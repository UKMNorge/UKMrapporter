<?php
namespace UKMNorge\Rapporter;
use Exception;
use UKMNorge\Arrangement\Skjema\Skjema;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Arrangement\UKMFestival;
use UKMNorge\Arrangement\Arrangement;
use UKMrapporter;
use UKMNorge\Geografi\Fylker;
use UKMNorge\Arrangement\Videresending\Ledere\Ledere;

class LedereOversikt extends Rapport
{
    public $kategori_id = 'ukmfestivalen';
    public $ikon = 'dashicons-businesswoman';
    public $navn = 'Ledere oversikt';
    public $beskrivelse = 'Informasjon om ledere';
    public $har_excel = false;
    public $har_sms = false;
    public $har_epost = false;
    
    /**
     * Er rapporten synlig
     * Rapporten er synlig bare på UKM Festivalen (land)
     * 
     * @return Array
     */
    public function erSynlig() {
        // Sjekk om arrangementet finnes. Metoden kan kalles fra kommuner eller fylker
        try {
            $arrangement = new Arrangement(get_option('pl_id'));
        }catch(Exception $e) {
            return false;
        }
        if($arrangement->getEierType() == 'land') {
            return true;
        }
        return false;
    }
    /**
     * Data til "tilpass rapporten"
     * 
     * @return Array
     */
    public function getCustomizerData()
    {
        return ['alleFylker' => Fylker::getAll()];
    }
    /**
     * Hent hvilken template som skal benyttes
     *
     * @return String $template_id
     */
    public function getTemplate()
    {   
        // Fylker
        $selectedFylker = [];
        // hvis brukeren velger ingen av alternativene så skal alle fylker vises
        if(count($this->getConfig()->getAll()) < 1) {
            $selectedFylker = Fylker::getAll();
        }

        foreach($this->getConfig()->getAll() as $selectedItem) {
            if($selectedItem->getId() == 'vis_fylke_alle') {
                $selectedFylker = [];
                $selectedFylker = Fylker::getAll();
                break;
            }
            try{
                // struktur av strengen er: 'vis_fylke_00'
                $fylkeId = (int) explode("_", $selectedItem->getId(), 3)[2];
                $selectedFylker[] = Fylker::getById($fylkeId);
            }catch(Exception $e) {
                throw new Exception(
                    'Beklager, fylke kunne ikke leses' . $selectedItem->getId(),
                    100001003
                );
            }
        }
        // Ledere
        $fylkeLedere = [];
        $til = new Arrangement(get_option('pl_id'));
        
        foreach($til->getVideresending()->getAvsendere() as $avsender) {
            foreach($selectedFylker as $fylke) {
                // Check if fylke has been selected
                $fra = $avsender->getArrangement();
                // var_dump($fra->getFylke());
                if($fylke->getId() == $fra->getFylke()->getId()) {
                    $ledere = new Ledere($fra->getId(), $til->getId());

                    $fylkeLedere[$fylke->getId()]['fylke'] = $fylke;
                    $fylkeLedere[$fylke->getId()]['fra'] = $fra;
                    foreach($ledere->getAll() as $leder) {
                        // turist, ledsager og sykerom blir ikke med i rapporten
                        if(!in_array($leder->getType(), ['turist', 'ledsager', 'sykerom'])) {
                            $fylkeLedere[$fylke->getId()]['ledere'][] = $leder;
                        }
                    }
                }
            }
        }
        // var_dump($fylkeLedere);
        // die;
        UKMrapporter::addViewData('fylkeLedere', $fylkeLedere);
        return 'LedereOversikt/rapport.html.twig';
    }
}