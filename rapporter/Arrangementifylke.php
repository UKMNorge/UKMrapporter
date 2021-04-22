<?php

namespace UKMNorge\Rapporter;

use Exception;
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
    public $har_epost = true;
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
        return 'Arrangementifylke/rapport.html.twig';
    }

    public function getArrangementer(Kommune $kommune) {
        $arrangementer = [];

        $omrade = $kommune->getOmrade();
        
        foreach( $omrade->getArrangementer()->getAll() as $arrangement) {
            foreach($arrangement->getKommuner() as $km) {
                $arrangementer[] = $arrangement;
            }
        }

        return $arrangementer;
    }

    public function getRenderData() {
        $data = new Gruppe('arrangementifylke', 'Arrangementifylke');        
        return $data;
    }

    /**
     * Hent excel-fil (og få med arket titler)
     *
     * @inheritdoc
     */
    public function getExcelFile()
    {
        $this->getConfig()->get('vis_titler')->setValue(true);
        return parent::getExcelFile();
    }
}