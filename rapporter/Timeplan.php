<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Geografi\Fylker;

use UKMrapporter;

class Timeplan extends Rapport
{
    public $kategori_id = 'ukmfestivalen';
    public $ikon = 'dashicons-clock';
    public $navn = 'Timeplan';
    public $beskrivelse = 'Informasjon om oppmøtetid.';
    public $krever_hendelse = false;
    public $har_excel = false;

    /**
     * Data til "tilpass rapporten"
     * 
     * @return Array
     */
    public function getCustomizerData() {
        return ['alleFylker' => Fylker::getAll()];
    }

    
    public function getTemplate() {
        $grupper = new Gruppe('container', 'Alle innslag');
        $grupper->setVisOverskrift(false);

        $sortering_metode = '';

        $personerInnslag = [];
        switch ($this->getConfig()->get('grupper')) {
            case 'alfabetisk':
                $sortering_metode = 'alfabetisk';
                foreach( $this->getArrangement()->getInnslag()->getAll() as $innslag ) {
                    foreach( $innslag->getPersoner()->getAll() as $person ) {
                        $personerInnslag[] = ['person' => $person, 'innslag' => $innslag];
                    }
                }
                break;

            case 'innslag':
                $sortering_metode = 'innslag';
                foreach( $this->getArrangement()->getInnslag()->getAll() as $innslag ) {
                    foreach( $innslag->getPersoner()->getAll() as $person ) {
                        $personerInnslag[$innslag->getType()->getNavn()]['personer'][] = $person;
                    }

                    $personerInnslag[$innslag->getType()->getNavn()]['innslag'] = $innslag;
                }
                break;

            default:
            $grupper->setInnslag($this->getArrangement()->getInnslag()->getAll());
            break;
        }

        UKMrapporter::addViewData('sorteringMetode', $sortering_metode);
        UKMrapporter::addViewData('personerInnslag', $personerInnslag);
        return 'Timeplan/rapport.html.twig';
    }
}