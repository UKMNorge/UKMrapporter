<?php

namespace UKMNorge\Rapporter;

use Exception;
use UKMrapporter;
use UKMNorge\Geografi\Kommune;
use UKMNorge\Nettverk\Administrator as NetverkAdministrator;
use UKMNorge\Nettverk\Omrade;
use UKMNorge\Rapporter\Framework\Rapport;

class Arrangementifylke extends Rapport
{
    public $kategori_id = 'user_kontakt';
    public $ikon = 'dashicons-buddicons-groups';
    public $navn = 'Arrangementene dine';
    public $beskrivelse = 'Arrangemener i kommuner eller fylker du har tilgang til';
    public $har_excel = false;
    public $har_epost = false;
    public $har_sms = false;
    public $arrangementer = [];

    /**
     * Hent alle fylke-områder current user kan administrere
     *
     * @return Array<Omrade>
     */
    public function getOmrader() {
        $admin = new NetverkAdministrator(get_current_user_id());

        foreach( $admin->getOmrader() as $omrade ) {
            try {
                $fylker[ $omrade->getFylke()->getId() ] = $omrade->getFylke();
            } catch( Exception $e ) {
                if( $e->getCode() != 162001 ) {
                    throw $e;
                }
            }
        }

        return $fylker;
    }

    /**
     * Hent sessonger fra $fra til og med dette året
     *
     * @return Array<Int>
     */
    public function getSessongArr(int $fra) {
        $sessonger = [];      
        $aar = (int)Date('Y');
        while($aar > $fra) {
            $sessonger[] = $aar;
            $aar--;
        }

        return $sessonger;
    }

    /**
     * Hent hvilken template som skal benyttes
     *
     * @return String $template_id
     */
    public function getTemplate() {
        $admin = new NetverkAdministrator(get_current_user_id());
        $kommuner = [];
        $arrangementer = [];

        // OBS: forsiktig her, O(n^3) notasjon. Dette kan føre til ytelsesreduksjon spesielt med store mendgder av data. 
        foreach( $admin->getOmrader() as $omrade ) {
            $fylke = $omrade->getFylke();
            foreach( $fylke->getKommuner()->getAll() as $kommune ) {
                
                if( !$this->getConfig()->vis('valgte_kommuner_i_'. $fylke->getId()) && !$this->getConfig()->vis('kommune_'. $kommune->getId() )) {
                    continue;
                }

                // Hvis det er alfabetisk eller sortering av arrangementer i kommune
                if($this->getConfig()->get('sortering') == 'alfabetisk' || $this->getConfig()->get('sortering') == 'dato_innad_kommune') {
                    $arrangementerIKommune = $this->getArrangementer($kommune);
                    // Sorter arrangementer innad kommune
                    if($this->getConfig()->get('sortering') == 'dato_innad_kommune') {
                        usort($arrangementerIKommune, function($a, $b) { return $a->start <=> $b->start; });
                    }
                    
                    $kommuner[] = [
                        'kommune' => $kommune,
                        'arrangementer' => $arrangementerIKommune
                    ];
                } 
                // Legg til arrangementer i listen
                else {
                    $kommuneArrangementer = $this->getArrangementer($kommune);
                    
                    $kommuner[] = [
                        'kommune' => $kommune,
                        'arrangementer' => $kommuneArrangementer
                    ];

                    $arrangementer[] = $kommuneArrangementer;
                }
            }
        }

        

        // Sorter arrangemeter når sortering er 'start_dato' 
        if($this->getConfig()->get('sortering') == 'start_dato') {
            // Konvert til 1-dimensjonal liste
            $arrangementer = $this->flatten($arrangementer);
            // Sort ved hjelp av usort og callback funksjon. For å endre fra ASC til DESC bare bytt $a med $b i funksjonens attributer
            usort($arrangementer, function($a, $b) { return $a->start <=> $b->start; });
        }

        UKMrapporter::addViewData('kommuner', $kommuner);
        UKMrapporter::addViewData('arrangementer', $arrangementer);
        UKMrapporter::addViewData('tidNaa', strtotime(Date("Y-m-d H:i:s")));

        return 'Arrangementifylke/rapport.html.twig';
    }

    public function getArrangementer(Kommune $kommune) {
        $arrangementer = [];
        
        foreach( $kommune->getOmrade()->getArrangementer()->getAll() as $arrangement) {
            $arrangementer[] = $arrangement;
        }

        return $arrangementer;
    }

    // Flatten array. Altså fra multidimensjonal til 1-dimensjonal array
    private function flatten(array $array) {
        $return = array();
        array_walk_recursive($array, function($arr) use (&$return) { $return[] = $arr; });
        return $return;
    }
}