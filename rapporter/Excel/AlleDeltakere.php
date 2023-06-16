<?php

namespace UKMNorge\Rapporter\Excel;

use UKMNorge\Rapporter\Framework\Config;
use UKMNorge\Rapporter\Framework\Excel;
use UKMNorge\File\Excel as ExcelDok;
use UKMNorge\Allergener\Allergener;
use UKMNorge\UKMFestivalen\Overnatting\OvernattingPerson as OvernattingPersonClass;




class AlleDeltakere extends Excel {

    public function __construct( String $navn, Array $data, Config $config ) {
        $this->config = $config;
        $this->excel = new ExcelDok( $navn );
        
        $sorteringMetode = $data['sorteringMetode'];
        $personerInnslag = $data['personerInnslag'];
        $alleInnslag = $data['alleInnslag'];

        $this->rad();
        $kolonne = $this->celle('A', 'Deltaker navn');
        $kolonne = $this->celle('B', 'Mobil');
        $kolonne = $this->celle('C', 'Innslag navn');
        $kolonne = $this->celle('D', 'Innslag type');
        $kolonne = $this->celle('E', 'Kommune');
        $kolonne = $this->celle('F', 'Fylke');

        if($sorteringMetode == 'alfabetisk') {
            $counterShow = 0;
            $uniqueDeltaker = [];
            $counter = 0;

            usort($personerInnslag, function($a, $b) { return $a['person']->getNavn() > $b['person']->getNavn() ? 1 : ($b['person']->getNavn() < $a['person']->getNavn() ? -1 : 0); });

            foreach ($personerInnslag as $persInnslag) {
                $person = $persInnslag['person'];
                $innslag = $persInnslag['innslag'];

                if($person->getMobil()) {
                    $uniqueDeltaker[$person->getNavn() . '_' . $person->getMobil()] = $person->getMobil();
                }
                else {
                    $counter++; 
                }

                $counterShow++;
                
                $this->rad();
                $kolonne = $this->celle('A', $counterShow . '. ' . $person->getNavn());
                $kolonne = $this->celle('B', $person->getMobil());
                $kolonne = $this->celle('C', $innslag->getNavn());
                $kolonne = $this->celle('D', $innslag->getType());
                
                if($person->getKommune() && $person->getKommune() != 'ukjent') {
                    $kolonne = $this->celle('E', $person->getKommune()->getNavn());
                } else {
                    $kolonne = $this->celle('E', $innslag->getKommune()->getNavn());
                }
                
                if($person->getFylke()) { 
                    $kolonne = $this->celle('F', $person->getFylke()->getNavn());
                } else {
                    $kolonne = $this->celle('F', $innslag->getFylke()->getNavn());
                }
                
            }

            $this->rad();
            $this->rad();
            $kolonne = $this->celle('A', 'Antall unike deltakere');
            $kolonne = $this->celle('B', $counter + count($uniqueDeltaker));
        }
        else {
            $uniqueTotal = 0;
            $allePersoner = [];
            

            foreach ($personerInnslag as $key => $persIns) {
                $personer = $persIns['personer'];
                $counterShow = 0;
                $uniqueDeltaker = [];
                $counter = 0;
                

                $this->excel->setArk($key, $key);

                if($personer) {
                    usort($personer, function($a, $b) { return $a->getNavn() > $b->getNavn() ? 1 : ($b->getNavn() < $a->getNavn() ? -1 : 0); });

                    foreach ($personer as $person) {
                    
                        $allePersoner[] = $person;
                    
                        if($person->getMobil()) {
                            $uniqueDeltaker[$person->getNavn() . '_' . $person->getMobil()] = $person->getMobil();
                        }
                        else {
                            $counter++; 
                        }

                        $innslag = $alleInnslag[$person->getContext()->getInnslag()->getId()];

                        $counterShow++;

                        $this->rad();
                        $kolonne = $this->celle('A', $counterShow . '. ' . $person->getNavn());
                        $kolonne = $this->celle('B', $person->getMobil());
                        $kolonne = $this->celle('C', $innslag->getNavn());
                        $kolonne = $this->celle('D', $innslag->getType());

                        if($person->getKommune() && $person->getKommune() != 'ukjent') {
                            $kolonne = $this->celle('E', $person->getKommune()->getNavn());
                        } else {
                            $kolonne = $this->celle('E', $innslag->getKommune()->getNavn());
                        }
                        
                        if($person->getFylke()) {
                            $kolonne = $this->celle('F', $person->getFylke()->getNavn());
                        } else {
                            $kolonne = $this->celle('F', $innslag->getFylke()->getNavn());
                        }
                    }
                }
                
                $this->rad();
                $this->rad();
                $kolonne = $this->celle('A', 'Unike deltakere i ' .  ($sorteringMetode != 'innslag' ? $key : strtolower($key)) . ':');
                $kolonne = $this->celle('B', $counter + count($uniqueDeltaker));

                $uniqueTotal = $uniqueTotal + $counter + count($uniqueDeltaker);
                
            }

            $this->excel->setArk('totalt', 'Totalt');

            $alleUniqueDeltaker = [];
            
            foreach ($allePersoner as $pers) {
                if($pers->getMobil()) {
                    $alleUniqueDeltaker[$pers->getNavn() . '_' . $pers->getMobil()] = $pers->getMobil();
                } else {
                    $counter = $counter + 1; 
                }

            }
            
            $this->rad();
            $kolonne = $this->celle('A', 'Antall unike deltakere: ');
            $kolonne = $this->celle('B', $counter + count($alleUniqueDeltaker));
        }


    }
}

