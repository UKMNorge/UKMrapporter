<?php

namespace UKMNorge\Rapporter\Framework;

use UKMNorge\File\Excel as ExcelDok;

class ExcelPersoner extends Excel {

    public function __construct( String $navn, Array $alle_personer, Config $config ) {
        $this->config = $config;
        $this->excel = new ExcelDok( $navn );

        $this->setArk('personer', 'Personer');
        $this->rad();
        
        $this->celle('A', 'Fornavn');
        $kolonne = $this->celle('B', 'Etternavn');
        $kolonne = $this->celleHvis('deltakere_mobil', 'Mobil', $kolonne);
        $kolonne = $this->celleHvis('deltakere_alder', 'Alder', $kolonne);
        $kolonne = $this->celleHvis('deltakere_rolle', 'Rolle', $kolonne);
        $kolonne = $this->celleHvis('fylke', 'Fylke', $kolonne);
        $kolonne = $this->celleHvis('kommune', 'Kommune', $kolonne);
        $this->fet('A'. $this->getRad() .':'. $kolonne . $this->getRad());

        /** DATA */
        foreach( $alle_personer as $person ) {
            $this->rad();
            $kolonne = $this->celle('A', $person->getFornavn());
            $kolonne = $this->celle('B', $person->getEtternavn());
            $kolonne = $this->celleHvis('deltakere_mobil', $person->getMobil(), $kolonne);
            $kolonne = $this->celleHvis('deltakere_alder', $person->getAlder(''), $kolonne);
            $kolonne = $this->celleHvis('deltakere_rolle', $person->getRolle(), $kolonne);
            $kolonne = $this->celleHvis('fylke', $person->getKommune()->getFylke()->getNavn(), $kolonne);
            $kolonne = $this->celleHvis('kommune', $person->getKommune()->getNavn(), $kolonne);
        }
    }
}
