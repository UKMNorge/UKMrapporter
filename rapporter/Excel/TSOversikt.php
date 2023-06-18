<?php

namespace UKMNorge\Rapporter\Excel;

use UKMNorge\Rapporter\Framework\Config;
use UKMNorge\Rapporter\Framework\Excel;
use UKMNorge\File\Excel as ExcelDok;
use UKMNorge\Allergener\Allergener;
use UKMNorge\Arrangement\Arrangement;
use UKMNorge\UKMFestivalen\Overnatting\OvernattingPerson as OvernattingPersonClass;




class TSOversikt extends Excel {

    public function __construct( String $navn, Array $data, Config $config ) {
        $this->config = $config;
        $this->excel = new ExcelDok( $navn );

        $til = new Arrangement(get_option('pl_id'));
        $selectedFylker = $data;
        $alleFylker = [];


        // Alle arrangementer som ble videresend til $til
        $personer = [];
        $counter = 0;
        foreach($til->getVideresending()->getAvsendere() as $avsender) {
            // For alle fylker som ble valgt
            foreach($selectedFylker as $fylke) {
                // var_dump('a ' . $fylke->getId());
                $alle_selected_fylker[$fylke->getId()] = $fylke;

                $fra = $avsender->getArrangement();

                // Hvis $fra (arrangement som ble videresendt) er fra fylke som er valgt
                if($fylke->getId() == $fra->getFylke()->getId()) { 
                    $alleFylker[$fylke->getId()] = $fylke;   
                    foreach($avsender->getVideresendte()->getAll() as $innslag) {
                        foreach ($innslag->getPersoner()->getAll() as $person) {
                            $personer[$fylke->getId()][$fra->getId()][] = $person;
                            $counter++;
                        }
                    }
                }
            }            
        }

        $this->rad();
        $kolonne = $this->celle('A', 'Deltaker navn');
        $kolonne = $this->celle('B', 'Mobilnummer');
        $kolonne = $this->celle('C', 'Alder');
        $kolonne = $this->celle('D', 'Fylke');

        foreach ($personer as $fylkeId => $arrangementer) {
            $fylke = $alleFylker[$fylkeId];
            foreach ($arrangementer as $arrId => $person) {
                $arrang = new Arrangement($arrId);
                $this->excel->setArk($arrId, substr($arrang->getNavn(), 0, 30));
                foreach ($person as $p) {
                    $this->rad();
                    $kolonne = $this->celle('A', $p->getNavn());
                    $kolonne = $this->celle('B', $p->getMobil());
                    $kolonne = $this->celle('C', $p->getAlder());
                    $kolonne = $this->celle('D', $fylke->getNavn());
                }
            }
        }


    }
}

