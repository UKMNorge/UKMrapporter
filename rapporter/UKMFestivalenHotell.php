<?php

namespace UKMNorge\Rapporter;

use Exception;
use UKMNorge\Arrangement\Skjema\Skjema;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Arrangement\UKMFestival;
use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Geografi\Fylker;
use UKMNorge\Arrangement\Videresending\Ledere\Ledere;
use UKMNorge\Rapporter\Excel\UKMFestivalenHotell as ExcelUKMFestivalenHotell;

use DateTime;


use UKMrapporter;



class UKMFestivalenHotell extends Rapport
{
    public $kategori_id = 'ukmfestivalen';
    public $ikon = 'dashicons-admin-multisite';
    public $navn = 'UKM Festivalen Hotell';
    public $beskrivelse = 'Informasjon om overnattinger, romtype osv.';
    public $har_excel = true;
    public $har_sms = false;
    public $har_epost = false;
    
    private $table_name = 'ukm_festival_overnatting_gruppe';
	private $table_idcol = 'id';
	private $object_type = 'gruppe';
	private $filter = '';

    
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
        $arrangement = $this->getArrangement();

        $fylker = Fylker::getAll();
        try {
            if($arrangement->getEierType() == 'land') {
                $arrangement = new UKMFestival($arrangement->getId());
                return ['alleFylker' => $fylker, 'skjema' => $this->getArrangement()->getSkjema(), 'overnattingGrupper' => $arrangement->getOvernattingGrupper()];
            }
            
            return ['alleFylker' => $fylker, 'skjema' => $this->getArrangement()->getSkjema()];
        } catch (Exception $e) {
            if ($e->getCode() != 151002) {
                throw $e;
            }
        }
        return ['alleFylker' => $fylker];
    }

    /**
     * Data til rapporten
     * 
     * @return Array
     */
    public function getRenderDataArray() {
        $til = $this->getArrangement();

        if($til->getEierType() == 'land') {
            $til = new UKMFestival($til->getId());
            UKMrapporter::addViewData('overnattingGrupper', $til->getOvernattingGrupper());
        }


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
                break;
            }
            try{
                if(strpos($selectedItem->getId(), 'vis_fylke') !== false) {
                    // struktur av strengen er: 'vis_fylke_00'
                    $fylkeId = (int) explode("_", $selectedItem->getId(), 3)[2];
                    $selectedFylker[] = Fylker::getById($fylkeId);
                }
            }catch(Exception $e) {
                throw new Exception(
                    'Beklager, fylke kunne ikke leses ' . $selectedItem->getId(),
                    100001004
                );
            }
        }

        $arrangementer = [];
        $netter = [];
        $kommentarer = [];
        $alleGyldigeNetter = [];
        $alleLedere = 0;
        
        $forstGyldigNatt = null;
        $sisteGyldigNatt = null;

        foreach($til->getNetter() as $natt) {
            if($forstGyldigNatt == null) {
                $forstGyldigNatt = $natt;
            }
            $alleGyldigeNetter[$natt->format('d_m')] = $natt;

            $sisteGyldigNatt = $natt;
        }

        
        // Legg til 30 netter i forkant. Dette er definert på overnatting i UKM-festivalen på arr.sys
        $fNatt = new DateTime($forstGyldigNatt->format('Y-m-d H:i:s'));
        for($i = 0; $i < 30; $i++) {
            $natt = $fNatt->modify('-1 day');
            $newNatt = new DateTime($natt->format('Y-m-d H:i:s'));
            $alleGyldigeNetter[$natt->format('d_m')] = $newNatt;
        }

        // Legg til 30 netter i etterkant. Dette er definert på overnatting i UKM-festivalen på arr.sys
        $eNatt = new DateTime($sisteGyldigNatt->format('Y-m-d H:i:s'));
        for($i = 30; $i > 0; $i--) {
            $natt = $eNatt->modify('+1 day');
            $newNatt = new DateTime($natt->format('Y-m-d H:i:s'));
            $alleGyldigeNetter[$natt->format('d_m')] = $newNatt;
        }

        // Alle arrangementer som ble videresend til $til
        foreach($til->getVideresending()->getAvsendere() as $avsender) {
            $fra = $avsender->getArrangement();
            $kommentarer[$fra->getFylke()->getId()][$fra->getId()] = $fra->getMetaValue('kommentar_overnatting_til_' . $til->getId());
            
            // For alle fylker som ble valgt
            foreach($selectedFylker as $fylke) {
                $fylker[$fylke->getId()] = $fylke;                

                // Hvis $fra (arrangement som ble videresendt) er fra fylke som er valgt
                if($fylke->getId() == $fra->getFylke()->getId()) {
                    $arrangementer[$fra->getId()] = $fra;
                    
                    $ledere = new Ledere($fra->getId(), $til->getId());

                    foreach($ledere->getAll() as $leder) {
                    
                        foreach($leder->getNetter()->getAll() as $natt) {
                            // Hvis natten er del av gyldige netter for 'til' arrangementet og sted er hotell
                            if($alleGyldigeNetter[$natt->getId()] && $natt->getSted() == 'hotell') {
                                // Bare ledere som overnatter i landsbyen skal bli med i rapporten
                                $netter[$natt->getId()]['fylker'][$fylke->getId()][$fra->getId()][] = $leder;
                                $alleLedere++;
                            }
                        }
                    }
                }
            }
        }

        $arrangement = new Arrangement(get_option('pl_id'));
        $arrangement = new UKMFestival($arrangement->getId());
        foreach($arrangement->getOvernattingGrupper() as $og) {
            foreach($og->getAllePersoner() as $person) {
                $ankomst = new DateTime(str_replace('.', '-', $person->getAnkomst() . '-' . date("Y")));
                $avreise = new DateTime(str_replace('.', '-', $person->getAvreise() . '-' . date("Y")));

                for($i = $ankomst; $i <= $avreise; $i->modify('+1 day')){
                    $netter[$i->format("d_m")]['fylker'][0][$person->getRom() ? $person->getRom()->getId() : $person->getId()][$person->getId()] = $person;
                }
            }
        }
        

        // Sorterer gyldige netter
        usort($alleGyldigeNetter, function($a, $b) {
            return $a->getTimestamp() - $b->getTimestamp();
        });

        return [
            'netter' => $netter,
            'fylker' => $fylker,
            'arrangementer' => $arrangementer,
            'kommentarer' => $kommentarer,
            'alleGyldigeNetter' => $alleGyldigeNetter,
            'alleLedere' => $alleLedere
        ];
    }

    /**
     * Hent hvilken template som skal benyttes
     *
     * @return String $template_id
     */
    public function getTemplate()
    {

        $data = $this->getRenderDataArray();

        UKMrapporter::addViewData('netter', $data['netter']);
        UKMrapporter::addViewData('fylker', $data['fylker']);
        UKMrapporter::addViewData('arrangementer', $data['arrangementer']);
        UKMrapporter::addViewData('kommentarer', $data['kommentarer']);
        UKMrapporter::addViewData('alleGyldigeNetter', $data['alleGyldigeNetter']);
        UKMrapporter::addViewData('alleLedere', $data['alleLedere']);

        return 'UKMFestivalenHotell/rapport.html.twig';

    }

     /**
     * Lag og returner excel-filens URL
     * 
     * @return String url
     */
    public function getExcelFile()
    {
        $excel = new ExcelUKMFestivalenHotell(
            $this->getNavn() . ' oppdatert ' . date('d-m-Y') . ' kl '. date('Hi') . ' - ' . $this->getArrangement()->getNavn(),
            $this->getRenderDataArray(),
            $this->getConfig()
        );
        return $excel->writeToFile();
    }
}
