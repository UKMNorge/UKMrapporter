<?php

namespace UKMNorge\Rapporter\Excel;

use UKMNorge\Rapporter\Framework\Config;
use UKMNorge\Rapporter\Framework\Excel;
use UKMNorge\File\Excel as ExcelDok;
use UKMNorge\Allergener\Allergener;
use UKMNorge\UKMFestivalen\Overnatting\OvernattingPerson as OvernattingPersonClass;
use DateTimeImmutable;



class UKMFestivalenHotell extends Excel {

    public function __construct( String $navn, Array $data, Config $config ) {
        $this->config = $config;
        $this->excel = new ExcelDok( $navn );
        
        $antallBestillinger = 0;
        $ledere = [];

        $netter = $data['netter'];
        $fylker = $data['fylker'];
        $alleGyldigeNetter = $data['alleGyldigeNetter'];

        $personerNetter = [];

        foreach($alleGyldigeNetter as $gyldigNatt) {
            $nattKey = $gyldigNatt->format('d_m');
            $personerArr = [];
            $roomArr = [];
            $natt = $netter[$nattKey];
            $superTotal = 0;
            
            if ($natt) {
                foreach ($natt['fylker'] as $fylke_data) {
                    foreach ($fylke_data as $arr_data) {
                        $superTotal = $superTotal + count($arr_data);
                    }
                }
            
                $this->excel->setArk($nattKey, $gyldigNatt->format('d.m.y'));
                
                $this->rad();
                $kolonne = $this->celle('A', 'Navn');
                $kolonne = $this->celle('B', 'Tlf');
                $kolonne = $this->celle('C', 'Epost');
                $kolonne = $this->celle('D', 'Rom');
                $kolonne = $this->celle('E', 'Fylke');

                foreach ($natt['fylker'] as $fylke_key => $fylke_data) {
                    foreach ($fylke_data as $key_arr => $arr_data) {
                        foreach ($arr_data as $leder) {
                            $ledere[] = $leder;
                            $antallBestillinger = $antallBestillinger + 1;

                            // Legger til unique personer
                            $personerArr[$leder->getId()] = $leder;

                            // Legger til unique rom
                            $lederRomKey = $leder instanceof OvernattingPersonClass && $leder->getRom() ? $leder->getRom()->getId() : $leder->getId();
                            $roomArr[$lederRomKey] = $lederRomKey;

                            // instanceof brukes fordi leder kan komme fra flere klasser og det finnes tilfeller hvor ID kan være det samme for 2 ledere. 
                            $lederId = $leder instanceof OvernattingPersonClass ? 'OP'.$leder->getId() : $leder->getId();
                            $personerNetter[$lederId][] = $gyldigNatt;

                            $this->rad();
                            $kolonne = $this->celle('A', $leder->getNavn());
                            $kolonne = $this->celle('B', $leder->getMobil());
                            $kolonne = $this->celle('C', $leder->getEpost());
                            if($leder instanceof OvernattingPersonClass && $leder->getRom()) {
                                $delerRom = count(array_filter($arr_data, function($led) use ($leder) {
                                    return $led->getRom() && $led->getRom()->getId() == $leder->getRom()->getId();
                                })) > 1;

                                $kolonne = $this->celle('D', $leder->getRom()->getType() . ($delerRom ? ' (deler rom '. $leder->getRom()->getId() .' )' : ''));
                            }
                            else {
                                $kolonne = $this->celle('D', 'enkeltrom');
                            }
                            $kolonne = $this->celle('E', $fylker[$fylke_key] ? $fylker[$fylke_key] : 'UKM Norge');
                        }
                    }
                }

                $this->rad();
                $this->rad();
                $kolonne = $this->celle('A', 'Antall personer:');
                $kolonne = $this->celle('B', count($personerArr));

                $this->rad();
                $kolonne = $this->celle('A', 'Antall rom:');
                $kolonne = $this->celle('B', count($roomArr));
            }            
        }

        $totalEnkeltrom = 0;
        $totalDobbeltrom = 0;
        $totalTrippeltrom = 0;
        $totalKvadrupeltrom = 0;

        foreach ($alleGyldigeNetter as $gyldigNatt) {
            $nattKey = $gyldigNatt->format('d_m');
            $natt = $netter[$nattKey];

            $antallEnkeltrom = [];
            $antallDobbeltrom = [];
            $antallTrippeltrom = [];
            $antallKvadrupeltrom = [];
        
            if($natt){
                foreach ($natt['fylker'] as $fylke_key => $fylke_data) {
                    foreach ($fylke_data as $key_arr => $arr_data) {
                        foreach ($arr_data as $leder) {
                            if($leder instanceof OvernattingPersonClass && $leder->getRom()) {
                                $rom = $leder->getRom() ? $leder->getRom() : null;
                                $kapasitet = $leder->getRom() ? $leder->getRom()->getKapasitet() : 1;
    
                                if($leder && $kapasitet == 1 && $leder->getRom()->getId()) {
                                    $antallEnkeltrom[] = $leder->getRom()->getId();
                                }
                                else if($leder && $kapasitet == 2 && $leder->getRom()->getId()) {
                                    $antallDobbeltrom[] = $leder->getRom()->getId();
                                }
                                else if($leder && $kapasitet == 3 && $leder->getRom()->getId()) {
                                    $antallTrippeltrom[] = $leder->getRom()->getId();
                                }
                                else if($leder && $kapasitet == 3 && $leder->getRom()->getId()) {
                                    $antallKvadrupeltrom[] = $leder->getRom()->getId();
                                }
                            }
                            else {
                                $antallEnkeltrom[] = $leder->getId();
                            }
                        }
                    }
                }
            }

            $totalEnkeltrom = $totalEnkeltrom + count($antallEnkeltrom);
            $totalDobbeltrom = $totalDobbeltrom + count($antallDobbeltrom);
            $totalTrippeltrom = $totalTrippeltrom + count($antallTrippeltrom);
            $totalKvadrupeltrom = $totalKvadrupeltrom + count($antallKvadrupeltrom);    
        }

        usort($alleGyldigeNetter, function($a, $b) {
            return $a->getTimestamp() - $b->getTimestamp();
        });

        $this->excel->setArk('totalt', 'Totalt');
            
        $this->rad();
        $kolonne = $this->celle('A', 'Antall enkeltrom:');
        $kolonne = $this->celle('B', 'Antall dobbeltrom:');
        $kolonne = $this->celle('C', 'Antall trippeltrom:');
        $kolonne = $this->celle('D', 'Antall kvadrupeltrom:');

        $this->rad();
        $kolonne = $this->celle('A', $totalEnkeltrom);
        $kolonne = $this->celle('B', $totalDobbeltrom);
        $kolonne = $this->celle('C', $totalTrippeltrom);
        $kolonne = $this->celle('D', $totalKvadrupeltrom);


        // Ark - Alle personer
        $this->excel->setArk('alle', 'Alle personer');

        $this->rad();
        $kolonne = $this->celle('A', 'Navn');
        $kolonne = $this->celle('B', 'Tlf');
        $kolonne = $this->celle('C', 'Epost');
        $kolonne = $this->celle('D', 'Dager');


        foreach ($personerNetter as $lederKeyID => $netter) {
            $currentLeder = null;
            foreach ($ledere as $leder) {
                $lederId = $leder instanceof OvernattingPersonClass ? 'OP'.$leder->getId() : $leder->getId();
                if($lederId == $lederKeyID) {
                    $currentLeder = $leder;
                }
            }

            // Sortering datoer
            usort($netter, function($time1, $time2)
            {
                $time1 = $time1->format('d.m.Y');
                $time2 = $time2->format('d.m.Y');
                if (strtotime($time1) > strtotime($time2))
                    return 1;
                else if (strtotime($time1) < strtotime($time2)) 
                    return -1;
                else
                    return 0;
            });

            // Lager string
            $netterStr = '';
            foreach ($netter as $n) {
                // Convert DateTime object to string
                $dateString = $n->format('d.m.Y');
                    
                // Create DateTimeImmutable object from string
                $nextDay = new DateTimeImmutable($dateString);

                // Add one day
                $nextDay = $nextDay->modify('+1 day');
                $netterStr = $netterStr .'|'. $n->format('d.m.Y') .'->'. $nextDay->format('d.m.Y') .' ';
            }


            $this->rad();
            $kolonne = $this->celle('A', $currentLeder->getNavn());
            $kolonne = $this->celle('B', $currentLeder->getMobil());
            $kolonne = $this->celle('C', $currentLeder->getEpost());
            $kolonne = $this->celle('D', $netterStr . '|');
        }
    }
}