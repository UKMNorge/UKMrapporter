<?php

namespace UKMNorge\Rapporter\Excel;

use UKMNorge\Rapporter\Framework\Config;
use UKMNorge\Rapporter\Framework\Excel;
use UKMNorge\File\Excel as ExcelDok;
use UKMNorge\Allergener\Allergener;
use UKMNorge\UKMFestivalen\Overnatting\OvernattingPerson as OvernattingPersonClass;




class UKMFestivalenHotell extends Excel {

    public function __construct( String $navn, Array $data, Config $config ) {
        $this->config = $config;
        $this->excel = new ExcelDok( $navn );
        
        $antallBestillinger = 0;
        $ledere = [];

        $netter = $data['netter'];
        $fylker = $data['fylker'];
        $alleGyldigeNetter = $data['alleGyldigeNetter'];

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
                $kolonne = $this->celle('C', 'Rom');
                $kolonne = $this->celle('D', 'Fylke');

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

                            
                            $this->rad();
                            $kolonne = $this->celle('A', $leder->getNavn());
                            $kolonne = $this->celle('B', $leder->getMobil());
                            if($leder instanceof OvernattingPersonClass && $leder->getRom()) {
                                $kolonne = $this->celle('C', $leder->getRom()->getType());
                            }
                            else {
                                $kolonne = $this->celle('C', 'enkeltrom');
                            }
                            $kolonne = $this->celle('D', $fylker[$fylke_key] ? $fylker[$fylke_key] : 'UKM Norge');
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
                                    $antallEnkeltrom[$leder->getRom()->getId()] = $leder->getRom()->getId();
                                }
                                else if($leder && $kapasitet == 2 && $leder->getRom()->getId()) {
                                    $antallDobbeltrom[$leder->getRom()->getId()] = $leder->getRom()->getId();
                                }
                                else if($leder && $kapasitet == 3 && $leder->getRom()->getId()) {
                                    $antallTrippeltrom[$leder->getRom()->getId()] = $leder->getRom()->getId();
                                }
                                else if($leder && $kapasitet == 3 && $leder->getRom()->getId()) {
                                    $antallKvadrupeltrom[$leder->getRom()->getId()] = $leder->getRom()->getId();
                                }
                            }
                            else {
                                $antallEnkeltrom[$leder->getId] = $leder->getId();
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
    }
}