<?php

namespace UKMNorge\Rapporter\Framework;

use UKMNorge\File\Excel as ExcelDok;

class Excel {
    var $innslag = [];

    public function __construct( String $navn, Array $alle_innslag, Config $config ) {
        $this->config = $config;
        $this->excel = new ExcelDok( $navn );

        /** HEADERS */
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
        $kolonne = $this->celleHvis('deltakere', 'Deltakere', $kolonne);
        $kolonne = $this->celleHvis('kontaktperson', 'Kontaktperson', $kolonne);
        $kolonne = $this->celleHvis('titler', 'Titler', $kolonne);
        #$kolonne = $this->celleHvis('mediefiler', 'Har mediefiler', $kolonne);
        
        if( $config->vis('deltakere') ) {
            $this->setArk('personer', 'Personer');
            $this->rad();
            $this->celle('A', 'Innslag');
            $this->celle('B', 'Fornavn');
            $kolonne_personer = $this->celle('C', 'Etternavn');
            $kolonne_personer = $this->celleHvis('deltakere_mobil', 'Mobil', $kolonne_personer);
            $kolonne_personer = $this->celleHvis('deltakere_alder', 'Alder', $kolonne_personer);
            $kolonne_personer = $this->celleHvis('deltakere_rolle', 'Rolle', $kolonne_personer);
            
            $kolonne_personer = $this->celle($kolonne_personer, 'Kategori');
            $kolonne_personer = $this->celleHvis('kategori_og_sjanger', 'Sjanger', $kolonne_personer);
            $kolonne_personer = $this->celleHvis('varighet', 'Varighet (sekunder)', $kolonne_personer);
            $kolonne_personer = $this->celleHvis('fylke', 'Fylke', $kolonne_personer);
            $kolonne_personer = $this->celleHvis('kommune', 'Kommune', $kolonne_personer);
            $kolonne_personer = $this->celleHvis('beskrivelse', 'Beskrivelse', $kolonne_personer);
            $kolonne_personer = $this->celleHvis('tekniske_behov', 'Tekniske behov', $kolonne_personer);
        }
        if( $config->vis('kontaktperson') ) {
            $this->setArk('kontakt','Kontaktpersoner');
            $this->rad();
            $this->celle('A','Innslag');
            $this->celle('B', 'Fornavn');
            $kolonne_kontakt = $this->celle('C', 'Etternavn');
            $kolonne_kontakt = $this->celleHvis('kontakt_mobil', 'Mobil', $kolonne_kontakt);
            $kolonne_kontakt = $this->celleHvis('kontakt_alder', 'Alder', $kolonne_kontakt);
            $kolonne_kontakt = $this->celleHvis('kontakt_epost', 'E-post', $kolonne_kontakt);
            
            $kolonne_kontakt = $this->celle($kolonne_kontakt, 'Kategori');
            $kolonne_kontakt = $this->celleHvis('kategori_og_sjanger', 'Sjanger', $kolonne_kontakt);
            $kolonne_kontakt = $this->celleHvis('varighet', 'Varighet (sekunder)', $kolonne_kontakt);
            $kolonne_kontakt = $this->celleHvis('fylke', 'Fylke', $kolonne_kontakt);
            $kolonne_kontakt = $this->celleHvis('kommune', 'Kommune', $kolonne_kontakt);
            $kolonne_kontakt = $this->celleHvis('beskrivelse', 'Beskrivelse', $kolonne_kontakt);
            $kolonne_kontakt = $this->celleHvis('tekniske_behov', 'Tekniske behov', $kolonne_kontakt);    
        }

        if( $config->vis('titler') ) {
            $this->setArk('titler', 'Titler');
            $this->ark('titler');
            $this->rad();
            $kolonne_titler = $this->celle('A', 'Innslag');
            $kolonne_titler = $this->celle($kolonne_titler, 'Tittel');
            $kolonne_titler = $this->celleHvis('tittel_detaljer', 'Detaljer', $kolonne_titler);
            $kolonne_titler = $this->celleHvis('tittel_varighet', 'Varighet', $kolonne_titler);
            $kolonne_titler = $this->celleHvis('kategori_og_sjanger', 'Sjanger', $kolonne_titler);
            $kolonne_titler = $this->celleHvis('fylke', 'Fylke', $kolonne_titler);
            $kolonne_titler = $this->celleHvis('kommune', 'Kommune', $kolonne_titler);
            $kolonne_titler = $this->celleHvis('beskrivelse', 'Beskrivelse', $kolonne_titler);
            $kolonne_titler = $this->celleHvis('tekniske_behov', 'Tekniske behov', $kolonne_titler);
        }

        /** DATA */
        foreach( $alle_innslag as $innslag ) {
            if( $innslag->getType()->harTitler() ) {
                $varighet = $innslag->getVarighet()->getSekunder();
            } else {
                $varighet = 0;
            }
            if( is_object( $innslag->getKategori() ) ) {
                $kategori = $innslag->getKategori()->getNavn();
            } else {
                $kategori = ucfirst($innslag->getKategori());
            }
            // Overraskende ofte er kategorien innslag-typen
            if( empty($kategori) ) {
                $kategori = $innslag->getType()->getNavn();
            }
            $this->setArk('innslag');
            $this->rad();

            $kolonne = $this->celle('A', $innslag->getNavn());
            $kolonne = $this->celle($kolonne, $kategori);
            $kolonne = $this->celleHvis('kategori_og_sjanger', $innslag->getSjanger(), $kolonne);
            $kolonne = $this->celleHvis('varighet', $varighet, $kolonne);
            $kolonne = $this->celleHvis('fylke', $innslag->getFylke()->getNavn(), $kolonne);
            $kolonne = $this->celleHvis('kommune', $innslag->getKommune()->getNavn(), $kolonne);
            $kolonne = $this->celleHvis('beskrivelse', $innslag->getBeskrivelse(), $kolonne);
            $kolonne = $this->celleHvis('tekniske_behov', $innslag->getTekniskeBehov(), $kolonne);
            #$kolonne = $this->celleHvis('mediefiler', 'Har mediefiler', $kolonne);

            if( $config->vis('deltakere') ) {
                $kompakt_personer = '';
                foreach( $innslag->getPersoner()->getAll() as $person ) {
                    $kompakt_personer .= 
                        strtoupper( $person->getNavn() ) .
                        ($config->vis('deltakere_mobil') ? $person->getMobil() : '') .
                        ($config->vis('deltakere_alder') ? ' ('. $person->getAlder() .')' : '') .
                        ($config->vis('deltakere_rolle') ? ' - '. $person->getRolle() : '') .
                        ', '
                        ;
                    $this->ark('personer');
                    $this->rad();
                    $this->celle('A', $innslag->getNavn());
                    $this->celle('B', $person->getFornavn());
                    $kolonne_personer = $this->celle('C', $person->getEtternavn());
                    $kolonne_personer = $this->celleHvis('deltakere_mobil', $person->getMobil(), $kolonne_personer);
                    $kolonne_personer = $this->celleHvis('deltakere_alder', $person->getAlder(''), $kolonne_personer);
                    $kolonne_personer = $this->celleHvis('deltakere_rolle', $person->getRolle(), $kolonne_personer);

                    $kolonne_personer = $this->celle($kolonne_personer, $kategori);
                    $kolonne_personer = $this->celleHvis('kategori_og_sjanger', $innslag->getSjanger(), $kolonne_personer);
                    $kolonne_personer = $this->celleHvis('varighet', $varighet, $kolonne_personer);
                    $kolonne_personer = $this->celleHvis('fylke', $innslag->getFylke()->getNavn(), $kolonne_personer);
                    $kolonne_personer = $this->celleHvis('kommune', $innslag->getKommune()->getNavn(), $kolonne_personer);
                    $kolonne_personer = $this->celleHvis('beskrivelse', $innslag->getBeskrivelse(), $kolonne_personer);
                    $kolonne_personer = $this->celleHvis('tekniske_behov', $innslag->getTekniskeBehov(), $kolonne_personer);
                }
                $this->setArk('innslag');
                $kolonne = $this->celle( $kolonne, rtrim($kompakt_personer,', '));
            }

            if( $config->vis('kontaktperson') ) {
                $this->setArk('kontakt');
                $this->rad();
                $this->celle('A',$innslag->getNavn());
                $this->celle('B', $innslag->getKontaktperson()->getFornavn());
                $kolonne_kontakt = $this->celle('C', $innslag->getKontaktperson()->getEtternavn());
                $kolonne_kontakt = $this->celleHvis('kontakt_mobil', $innslag->getKontaktperson()->getMobil(), $kolonne_kontakt);
                $kolonne_kontakt = $this->celleHvis('kontakt_alder', $innslag->getKontaktperson()->getAlder(), $kolonne_kontakt);
                $kolonne_kontakt = $this->celleHvis('kontakt_epost', $innslag->getKontaktperson()->getEpost(), $kolonne_kontakt);

                $kolonne_kontakt = $this->celle($kolonne_kontakt, $kategori);
                $kolonne_kontakt = $this->celleHvis('kategori_og_sjanger', $innslag->getSjanger(), $kolonne_kontakt);
                $kolonne_kontakt = $this->celleHvis('varighet', $varighet, $kolonne_kontakt);
                $kolonne_kontakt = $this->celleHvis('fylke', $innslag->getFylke()->getNavn(), $kolonne_kontakt);
                $kolonne_kontakt = $this->celleHvis('kommune', $innslag->getKommune()->getNavn(), $kolonne_kontakt);
                $kolonne_kontakt = $this->celleHvis('beskrivelse', $innslag->getBeskrivelse(), $kolonne_kontakt);
                $kolonne_kontakt = $this->celleHvis('tekniske_behov', $innslag->getTekniskeBehov(), $kolonne_kontakt);

                $this->setArk('innslag');
                $kolonne = $this->celle( 
                    $kolonne,
                    strtoupper( $innslag->getKontaktperson()->getNavn() ) .
                        ($config->vis('kontakt_mobil') ? $innslag->getKontaktperson()->getMobil() : '') .
                        ($config->vis('kontakt_alder') ? ' ('. $innslag->getKontaktperson()->getAlder() .')' : '') .
                        ($config->vis('kontakt_epost') ? ' - '. $innslag->getKontaktperson()->getMobil() : '')
                );
            }

            if( $config->vis('titler') ) {
                $kompakt_titler = '';
                if($innslag->getType()->harTitler() ) {
                    foreach( $innslag->getTitler()->getAll() as $tittel ) {
                        $kompakt_titler .= 
                            strtoupper( $tittel->getTittel() ) .
                            ($config->vis('tittel_detaljer') ? $tittel->getParentes() : '') .
                            ($config->vis('tittel_varighet') && $innslag->getType()->harTid() ? ' ('. $tittel->getVarighet()->getHumanShort() .')' : '') .
                            ', ';
                        $this->ark('titler');
                        $this->rad();
                        $kolonne_titler = $this->celle('A', $innslag->getNavn());
                        $kolonne_titler = $this->celle($kolonne_titler, $tittel->getTittel());
                        $kolonne_titler = $this->celleHvis('tittel_detaljer', $tittel->getParentes(), $kolonne_titler);
                        $kolonne_titler = $this->celleHvis('tittel_varighet', $tittel->getVarighet()->getSekunder(), $kolonne_titler);
                        $kolonne_titler = $this->celleHvis('kategori_og_sjanger', $innslag->getSjanger(), $kolonne_titler);
                        $kolonne_titler = $this->celleHvis('fylke', $innslag->getFylke()->getNavn(), $kolonne_titler);
                        $kolonne_titler = $this->celleHvis('kommune', $innslag->getKommune()->getNavn(), $kolonne_titler);
                        $kolonne_titler = $this->celleHvis('beskrivelse', $innslag->getBeskrivelse(), $kolonne_titler);
                        $kolonne_titler = $this->celleHvis('tekniske_behov', $innslag->getTekniskeBehov(), $kolonne_titler);
                    }
                }
                $this->setArk('innslag');
                $kolonne = $this->celle( $kolonne, rtrim($kompakt_titler,', '));
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