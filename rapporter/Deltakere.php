<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Geografi\Fylker;
use UKMNorge\Innslag\Context\Context;

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
        $alleInnslag = [];

        $personerInnslag = [];
        $sortering_metode = $this->getConfig()->get('grupper');

        if($this->getConfig()->get('grupper') == 'alfabetisk') {
            foreach( $this->getArrangement()->getInnslag()->getAll() as $innslag ) {
                foreach( $innslag->getPersoner()->getAll() as $person ) {
                    $personerInnslag[] = ['person' => $person, 'innslag' => $innslag];
                }
            }
        }
        else {
            foreach( $this->getArrangement()->getInnslag()->getAll() as $innslag ) {
                $key = $sortering_metode == 'innslag' ? $innslag->getType()->getNavn() :
                      ($sortering_metode == 'fylke' ? $innslag->getFylke()->getNavn() : $innslag->getKommune()->getNavn());

                foreach( $innslag->getPersoner()->getAll() as $person ) {
                    $context = new Context('innslag');
                    $context->setInnslag($innslag->getId(), $innslag->getType());
                    $person->setContext($context);
                    $personerInnslag[$key]['personer'][] = $person;
                }

                $personerInnslag[$key]['innslag'] = $innslag;
            }
        }
        
        if($sortering_metode != 'alfabetisk') {
            ksort($personerInnslag);
            foreach( $this->getArrangement()->getInnslag()->getAll() as $innslag ) {
                $alleInnslag[$innslag->getId()] = $innslag;
            }
        }

        UKMrapporter::addViewData('sorteringMetode', $sortering_metode);
        UKMrapporter::addViewData('personerInnslag', $personerInnslag);
        UKMrapporter::addViewData('alleInnslag', $alleInnslag);
        return 'Deltakere/rapport.html.twig';
    }
}