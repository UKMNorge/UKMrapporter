<?php
namespace UKMNorge\Rapporter;
use Exception;
use UKMNorge\Arrangement\Skjema\Skjema;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Arrangement\UKMFestival;
use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Arrangement\Videresending\Ledere\Hovedledere;
use UKMrapporter;
use UKMNorge\Geografi\Fylker;
use UKMNorge\Arrangement\Videresending\Ledere\Ledere;
use UKMNorge\Rapporter\Excel\TSOversikt;


class TotaloverisktVideresendingFylke extends Rapport
{
    public $kategori_id = 'ukmfestivalen';
    public $ikon = 'dashicons-chart-bar';
    public $navn = 'Totaloversikt videresending fra fylkene';
    public $beskrivelse = 'Totaloversikt over innslag, deltakere, ledere og ledsager/turister';
    public $har_excel = true;
    public $har_sms = false;
    public $har_epost = false;
    

    /**
     * Returnerer alle selekterte fylker
     * 
     * @return Array
     */
    private function getRenderDataArray() {
        $til = new Arrangement(get_option('pl_id'));

        // Fylker
        $selectedFylker = [];
        // hvis brukeren velger ingen av alternativene så skal alle fylker vises
        if(count($this->getConfig()->getAll()) < 1) {
            $selectedFylker = Fylker::getAll();
        }

        // Valgte fylker
        foreach($this->getConfig()->getAll() as $selectedItem) {
            if($selectedItem->getId() == 'vis_fylke_alle') {
                $selectedFylker = [];
                $selectedFylker = Fylker::getAll();
                continue;
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

        return $selectedFylker;
    }

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

        // Valgte fylker
        foreach($this->getConfig()->getAll() as $selectedItem) {
            if($selectedItem->getId() == 'vis_fylke_alle') {
                $selectedFylker = [];
                $selectedFylker = Fylker::getAll();
                continue;
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

        $til = new Arrangement(get_option('pl_id'));
    
        $alle_selected_fylker = [];
        $fylker = [];
        $arrangementer = [];
        $alle_unike_personer = [];

        // Alle arrangementer som ble videresend til $til
        foreach($til->getVideresending()->getAvsendere() as $avsender) {
            // For alle fylker som ble valgt
            foreach($selectedFylker as $fylke) {
                $alle_selected_fylker[$fylke->getId()] = $fylke;

                $fra = $avsender->getArrangement();

                $arrangementer[$fra->getId()] = $fra;

                // Hvis $fra (arrangement som ble videresendt) er fra fylke som er valgt
                if($fylke->getId() == $fra->getFylke()->getId()) {                    
                    $fylker[$fylke->getId()][$fra->getId()]['antallInnslag'] = $this->getAntallInnslag($fra);
                    $fylker[$fylke->getId()][$fra->getId()]['antallDeltakere'] = $this->getAntallDeltakere($fra);
                    $fylker[$fylke->getId()][$fra->getId()]['antallUnikeDeltakere'] = $this->getAntallUnikeDeltakere($fra);
                    
                    $fylker[$fylke->getId()][$fra->getId()]['ikkeUnikeDeltakere'] = $this->getIkkeUnikeDeltakere($fra);

                    $fylker[$fylke->getId()][$fra->getId()]['antallLedere'] = $this->getAntallLedere($fra);
                    $fylker[$fylke->getId()][$fra->getId()]['antallLedsagerTurister'] = $this->getAntallLedsagerTurister($fra);
                    $fylker[$fylke->getId()][$fra->getId()]['innslagIArrangement'] = $this->getVideresendtInnslag($fra);
                }

                // Legger til alle unike personer
                $tilArrangement = new Arrangement(get_option('pl_id'));
                foreach($fra->getVideresendte($tilArrangement->getId())->getAll() as $innslag) {
                    foreach( $innslag->getPersoner()->getAll() as $person ) {
                        $alle_unike_personer[$person->getNavn() . $person->getMobil()] = $person;
                    }
                }
            }
        }
        
        
        UKMrapporter::addViewData('fylkerData', $fylker);
        UKMrapporter::addViewData('alleFylker', $alle_selected_fylker);
        UKMrapporter::addViewData('arrangementer', $arrangementer);
        UKMrapporter::addViewData('alleUnikePersoner', $alle_unike_personer);
        UKMrapporter::addViewData('arrangementTil', $til);
        UKMrapporter::addViewData('alleIkkeUnikeDeltakere', $this->alleIkkeUnikeDeltakere($selectedFylker));

        return 'TotaloverisktVideresendingFylke/rapport.html.twig';
    }
    

    /**
     * Get antall deltakere i videresending
     * 
     * @param Arrangement $arrangement
     * @return Int
     */
    private function getAntallDeltakere($fraArrangement): int {
        $personer = [];
        $tilArrangement = new Arrangement(get_option('pl_id'));

        
        foreach($fraArrangement->getVideresendte($tilArrangement->getId())->getAll() as $innslag) {
            foreach( $innslag->getPersoner()->getAll() as $person ) {
                $personer[] = $person;
            }
        }

        return count($personer);
    }

    /**
     * Get antall UNIKE deltakere i videresending
     * 
     * @param Arrangement $arrangement
     * @return Int
     */
    private function getAntallUnikeDeltakere($fraArrangement): int {
        $unike_personer = [];
        $tilArrangement = new Arrangement(get_option('pl_id'));

        foreach($fraArrangement->getVideresendte($tilArrangement->getId())->getAll() as $innslag) {
            foreach( $innslag->getPersoner()->getAll() as $person ) {
                $unike_personer[ $person->getNavn() . $person->getMobil() ] = $person;
            }
        }

        return count($unike_personer);
    }

     /**
     * Get UNIKE deltakere i videresending
     * 
     * @param Arrangement $arrangement
     * @return Int
     */
    private function getIkkeUnikeDeltakere($fraArrangement): array {
        $alle_personer = [];
        $ikke_unike_personer = [];
        $tilArrangement = new Arrangement(get_option('pl_id'));

        foreach($fraArrangement->getVideresendte($tilArrangement->getId())->getAll() as $innslag) {
            foreach( $innslag->getPersoner()->getAll() as $person ) {
                if(isset($alle_personer[ $person->getNavn() . $person->getMobil() ])) {
                    $ikke_unike_personer[ $person->getNavn() . $person->getMobil() ] = $person;
                }
                $alle_personer[ $person->getNavn() . $person->getMobil() ] = $person;
            }
        }

        return $ikke_unike_personer;
    }

    private function alleIkkeUnikeDeltakere(array $selectedFylker): array {
        $personer = [];
        $alle_personer = [];
        $alle_ikke_unike_personer = [];
        $til = new Arrangement(get_option('pl_id'));
        
        foreach($til->getVideresending()->getAvsendere() as $avsender) {
            // For alle fylker som ble valgt
            $fra = $avsender->getArrangement();

            foreach($selectedFylker as $fylke) {
                if($fylke->getId() == $fra->getFylke()->getId()) {                    
                    foreach($fra->getVideresendte($til->getId())->getAll() as $innslag) {
                        foreach( $innslag->getPersoner()->getAll() as $person ) {
                            if(isset($personer[ $person->getNavn() . $person->getMobil() ])) {
                                $alle_ikke_unike_personer[ $person->getNavn() . $person->getMobil() ] = [
                                    'person' => $person,
                                    'fra' => $fra,
                                    'fylke' => $fylke
                                ];
                            }

                            $personer[ $person->getNavn() . $person->getMobil() ] = [
                                'person' => $person,
                                'fra' => $fra,
                                'fylke' => $fylke
                            ];

                            $alle_personer[] = [
                                'person' => $person,
                                'fra' => $fra,
                                'fylke' => $fylke
                            ];
                        }
                    }
                }
            }
        }
        
        $ret_ikke_unike = [];

        foreach($alle_ikke_unike_personer as $person) {
            foreach($alle_personer as $person2) {
                if($person['person']->getId() == $person2['person']->getId()) {
                    $ret_ikke_unike[] = $person;
                }
            }
        }

        return $ret_ikke_unike;
    }

    /**
     * Get antall innslag i videresending
     * 
     * @param Arrangement $arrangement
     * @return Int
     */
    private function getAntallInnslag($fraArrangement): int {
        $tilArrangement = new Arrangement(get_option('pl_id'));

        return $fraArrangement->getVideresendte($tilArrangement->getId())->getAntall();
    }

    /**
     * Get antall ledere
     * 
     * @param Arrangement $fraArrangement
     * @return Int
     */
    private function getAntallLedere(Arrangement $fraArrangement): int {
        // Til arrangement
        $total = 0;
        $til = new Arrangement(get_option('pl_id'));
        $ledere = new Ledere($fraArrangement->getId(), $til->getId());

        foreach($ledere->getAll() as $leder) {
            // turist, ledsager og sykerom blir ikke med som ledere
            if(!in_array($leder->getType(), ['turist', 'ledsager', 'sykerom'])) {
                $total++;
            }    
        }
        
        return $total;
    }

    /**
     * Get antall lesager og turister
     * 
     * @param Arrangement $arrangement
     * @return Int
     */
    private function getAntallLedsagerTurister($fraArrangement): int {
        $total = 0;
        $til = new Arrangement(get_option('pl_id'));
        $ledere = new Ledere($fraArrangement->getId(), $til->getId());

        foreach($ledere->getAll() as $leder) {
            // turist, ledsager og sykerom blir ikke med som ledere
            if(in_array($leder->getType(), ['turist', 'ledsager'])) {
                $total++;
            }    
        }
        
        return $total;
    }

    /**
     * Get antall lesager og turister
     * 
     * @param Arrangement $fraArrangement
     * @return Int
     */
    private function getVideresendtInnslag($fraArrangement): array {
        $tilArrangement = new Arrangement(get_option('pl_id'));

        $innslagIArrang = [];
        
        foreach($fraArrangement->getVideresendte($tilArrangement->getId())->getAll() as $innslag) {
            $innslagIArrang[$innslag->getType()->getNavn()][] = $innslag;
        }

        return $innslagIArrang;
    }


    /**
     * Lag og returner excel-filens URL
     * 
     * @return String url
     */
    public function getExcelFile()
    {
        $excel = new TSOversikt (
            $this->getNavn() . ' oppdatert ' . date('d-m-Y') . ' kl '. date('Hi') . ' - ' . $this->getArrangement()->getNavn(),
            $this->getRenderDataArray(),
            $this->getConfig()
        );
        return $excel->writeToFile();
    }
}