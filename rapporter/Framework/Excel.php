<?php

namespace UKMNorge\Rapporter\Framework;

use UKMNorge\File\Excel as ExcelDok;

class Excel {
    var $innslag = [];

    public function __construct( String $navn, Array $alle_innslag, Config $config ) {
        $this->config = $config;
        $this->excel = new ExcelDok( $navn );

        $this->excel->setArk('innslag', 'Innslag');
        $this->rad();
        $this->celle('A', 'Innslag');
        $kolonne = $this->celle('B','Kategori');

        $kolonne = $this->celleHvis('kategori_og_sjanger', 'Sjanger', $kolonne);
        $kolonne = $this->celleHvis('varighet', 'Varighet (sekunder)', $kolonne);
        $kolonne = $this->celleHvis('fylke', 'Fylke', $kolonne);
        $kolonne = $this->celleHvis('kommune', 'Kommune', $kolonne);
        $kolonne = $this->celleHvis('beskrivelse', 'Beskrivelse', $kolonne);
        $kolonne = $this->celleHvis('tekniske_behov', 'Tekniske behov', $kolonne);
        #$kolonne = $this->celleHvis('mediefiler', 'Har mediefiler', $kolonne);
        
        if( $config->vis('deltakere') ) {
            $this->setArk('personer', 'Personer');
            $this->rad();
            $this->celle('A', 'Innslag');
            $this->celle('B', 'Fornavn');
            $kolonne = $this->celle('C', 'Etternavn');
            $kolonne = $this->celleHvis('deltakere_mobil', 'Mobil', $kolonne);
            $kolonne = $this->celleHvis('deltakere_alder', 'Alder', $kolonne);
            $kolonne = $this->celleHvis('deltakere_rolle', 'Rolle', $kolonne);
        }
        foreach( $alle_innslag as $innslag ) {
            if( $innslag->getType()->harTitler() ) {
                $varighet = $innslag->getVarighet()->getSekunder();
            } else {
                $varighet = 0;
            }
            if( is_object( $innslag->getKategori() ) ) {
                $kategori = $innslag->getKategori()->getNavn();
            } else {
                $kategori = $innslag->getKategori();
            }
            $this->setArk('innslag');
            $this->rad();
            $kolonne = $this->celle('A', $innslag->getNavn());
            $kolonne = $this->celle('B', $kategori, $kolonne);
            $kolonne = $this->celleHvis('kategori_og_sjanger', $innslag->getSjanger(), $kolonne);
            $kolonne = $this->celleHvis('varighet', $varighet, $kolonne);
            $kolonne = $this->celleHvis('fylke', $innslag->getFylke()->getNavn(), $kolonne);
            $kolonne = $this->celleHvis('kommune', $innslag->getKommune()->getNavn(), $kolonne);
            $kolonne = $this->celleHvis('beskrivelse', $innslag->getBeskrivelse(), $kolonne);
            $kolonne = $this->celleHvis('tekniske_behov', $innslag->getTekniskeBehov(), $kolonne);
            #$kolonne = $this->celleHvis('mediefiler', 'Har mediefiler', $kolonne);

            if( $config->vis('deltakere') ) {
                foreach( $innslag->getPersoner()->getAll() as $person ) {
                    $this->ark('personer');
                    $this->rad();
                    $this->celle('A', $innslag->getNavn());
                    $this->celle('B', $person->getFornavn());
                    $kolonne = $this->celle('C', $person->getEtternavn());
                    $kolonne = $this->celleHvis('deltakere_mobil', $person->getMobil(), $kolonne);
                    $kolonne = $this->celleHvis('deltakere_alder', $person->getAlder(''), $kolonne);
                    $kolonne = $this->celleHvis('deltakere_rolle', $person->getRolle(), $kolonne);
                }
            }
        }
    }

    public function writeToFile() {
        return $this->excel->writeToFile();
    }

    private function ark( String $id ) {
        return $this->excel->ark($id);
    }

    private function setArk( String $id, String$navn=null ) {
        return $this->excel->setArk( $id, $navn );
    }

    private function celleHvis($hvis, $verdi, $kolonne ) {
        if( !$this->config->vis( $hvis ) ) {
            return $kolonne;
        }
        $this->celle($kolonne, $verdi);
        return ++$kolonne;
    }
    private function celle($kolonne, $verdi) {
        $this->excel->celle($kolonne, $verdi);
        return ++$kolonne;
    }
    private function rad() {
        $this->excel->rad();
    }
}