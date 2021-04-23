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
    public $navn = 'Arrangement i fylket';
    public $beskrivelse = 'Arrangement i fylket';
    public $har_excel = false;
    public $har_epost = false;
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
     * @return Array<int>
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

        foreach( $admin->getOmrader() as $omrade ) {
            $fylke = $omrade->getFylke();
            foreach( $fylke->getKommuner()->getAll() as $kommune ) {
                
                if( !$this->getConfig()->vis('valgte_kommuner_i_'. $fylke->getId()) && !$this->getConfig()->vis('kommune_'. $kommune->getId() )) {
                    continue;
                }

                $kommuner[] = [
                    'kommune' => $kommune,
                    'arrangementer' => $this->getArrangementer($kommune)
                ];
            }
        }
        
        UKMrapporter::addViewData('kommuner', $kommuner);
        UKMrapporter::addViewData('tidNaa', strtotime(Date("Y-m-d H:i:s")));

        return 'Arrangementifylke/rapport.html.twig';
    }

    public function getArrangementer(Kommune $kommune) {
        $arrangementer = [];
        
        foreach( $kommune->getOmrade()->getArrangementer()->getAll() as $arrangement) {
            $arrangementer[] = $arrangement;
        }

        // Sorter arrangemeter når sortering er 'start_dato' 
        if($this->getConfig()->get('sortering') == 'start_dato') {
            $arrangementer = $this->array_sort($arrangementer, 'start');
        }

        return $arrangementer;
    }

    // Hentet fra https://www.php.net/manual/en/function.sort.php
    private function array_sort($array, $on, $order=SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();
    
        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                break;
                case SORT_DESC:
                    arsort($sortable_array);
                break;
            }
    
            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }
    
        return $new_array;
    }
}