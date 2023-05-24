<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Geografi\Fylker;

use UKMrapporter;

class Deltakere extends Rapport
{
    public $kategori_id = 'personer';
    public $ikon = 'dashicons-buddicons-buddypress-logo';
    public $navn = 'Alle deltakere';
    public $beskrivelse = 'Informasjon om deltakere som er påmeldt arrangementet.';
    public $krever_hendelse = false;
    public $har_excel = false;

    
    public function getTemplate() {
        $sortering_metode = '';

        $personerInnslag = [];
        $sortering_metode = $this->getConfig()->get('grupper');

        switch ($this->getConfig()->get('grupper')) {
            case 'alfabetisk':
                foreach( $this->getArrangement()->getInnslag()->getAll() as $innslag ) {
                    foreach( $innslag->getPersoner()->getAll() as $person ) {
                        $personerInnslag[] = ['person' => $person, 'innslag' => $innslag];
                    }
                }
                break;

            case 'innslag':
                foreach( $this->getArrangement()->getInnslag()->getAll() as $innslag ) {
                    foreach( $innslag->getPersoner()->getAll() as $person ) {
                        $personerInnslag[$innslag->getType()->getNavn()]['personer'][] = $person;
                    }

                    $personerInnslag[$innslag->getType()->getNavn()]['innslag'] = $innslag;
                }
                break;
        
            case 'fylke':
                foreach( $this->getArrangement()->getInnslag()->getAll() as $innslag ) {
                    foreach( $innslag->getPersoner()->getAll() as $person ) {
                        $personerInnslag[$innslag->getFylke()->getNavn()]['personer'][] = $person;
                    }

                    $personerInnslag[$innslag->getFylke()->getNavn()]['innslag'] = $innslag;
                }
                break;

            case 'kommune':
                foreach( $this->getArrangement()->getInnslag()->getAll() as $innslag ) {
                    foreach( $innslag->getPersoner()->getAll() as $person ) {
                        $personerInnslag[$innslag->getKommune()->getNavn()]['personer'][] = $person;
                    }

                    $personerInnslag[$innslag->getKommune()->getNavn()]['innslag'] = $innslag;
                }
                break;
        }
        
        if($sortering_metode != 'alfabetisk') {
            ksort($personerInnslag);
        }

        UKMrapporter::addViewData('sorteringMetode', $sortering_metode);
        UKMrapporter::addViewData('personerInnslag', $personerInnslag);
        return 'Deltakere/rapport.html.twig';

        
    }
}