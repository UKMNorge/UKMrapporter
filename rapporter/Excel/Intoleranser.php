<?php

namespace UKMNorge\Rapporter\Excel;

use UKMNorge\Rapporter\Framework\Config;
use UKMNorge\Rapporter\Framework\Excel;
use UKMNorge\File\Excel as ExcelDok;
use UKMNorge\Allergener\Allergener;


class Intoleranser extends Excel {

    public function __construct( String $navn, Array $alle_personer, Config $config ) {
        $this->config = $config;
        $this->excel = new ExcelDok( $navn );

        /** HEADERS */
        $this->excel->setArk('personer', 'Personer');
        $this->rad();
        $kolonne = $this->celle('A', 'Navn');
        $kolonne = $this->celle('B', 'Mobil');
        $kolonne = $this->celle('C', 'Kommentar');
        $kolonne = $this->celle('D', 'Oppsummert');
        foreach( Allergener::getAll() as $allergen ) {
            $kolonne = $this->celle($kolonne, $allergen->getNavn());
        }

        $this->rad();
        $kolonne = $this->celle('A','Sum');
        $kolonne = $this->celle('B', '');
        $kolonne = $this->celle('C', '');
        $kolonne = $this->celle('D', '');
        foreach( Allergener::getAll() as $allergen ) {
            $kolonne = $this->celle($kolonne, '=SUM('. $kolonne .'3:'.$kolonne.(sizeof($alle_personer)+2).')');
        }
        
        foreach( $alle_personer as $person ) {
            $this->rad();
            $kolonne = $this->celle('A', $person->getNavn());
            $kolonne = $this->celle('B', $person->getMobil());
            $kolonne = $this->celle('C', $person->getSensitivt()->getIntoleranse()->getTekst());
            $d = $person->getSensitivt()->getIntoleranse()->getListeHuman(true);
            $kolonne = $this->celle('D', $d ? $d : '-');
            foreach( Allergener::getAll() as $allergen ) {
                $kolonne = $this->celle(
                    $kolonne,
                    $person->getSensitivt()->getIntoleranse()->harDenne( $allergen->getId() ) ?
                        1 : 0
                );
            }
        }
    }
}