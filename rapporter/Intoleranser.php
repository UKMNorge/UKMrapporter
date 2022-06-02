<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Excel\Intoleranser as ExcelIntoleranser;
use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Sensitivt\Requester;
use UKMNorge\Sensitivt\Sensitivt;

class Intoleranser extends Rapport
{
    public $kategori_id = 'personer';
    public $ikon = 'dashicons-carrot';
    public $navn = 'Intoleranse / allergi';
    public $beskrivelse = 'Deltakernes allergier og intoleranser.';
    public $krever_hendelse = false;

    public function getRenderData()
    {
        Sensitivt::setRequester(
            new Requester(
                'wordpress', 
                wp_get_current_user()->ID,
                get_option('pl_id')
            )
        );

        $gruppe = new Gruppe('container', 'Deltakere med intoleranser / allergier');
        $gruppe->setVisOverskrift(true);

        // UKM landsfesitvalen - grupper etter fylker
        if($this->getArrangement()->getType() == 'land') {
            foreach( $this->getArrangement()->getInnslag()->getAll() as $innslag ) {
                foreach( $innslag->getPersoner()->getAll() as $person ) {
                    $fylke_gruppe_id = $innslag->getFylke()->getNavn() . '-' . $innslag->getFylke()->getId();
                    if( $person->getSensitivt()->getIntoleranse()->har() ) {
                        if (!$gruppe->harGruppe($fylke_gruppe_id)) {
                            $gruppe->addGruppe(
                                new Gruppe(
                                    $fylke_gruppe_id,
                                    $innslag->getFylke()->getNavn()
                                )
                            );
                        }
                        $gruppe->getGruppe($fylke_gruppe_id)->addPerson($person);
                    }
                }
            }
        }
        // Alle andre arrangementer
        else {
            foreach( $this->getArrangement()->getInnslag()->getAll() as $innslag ) {
                foreach( $innslag->getPersoner()->getAll() as $person ) {
                    if( !$person->getSensitivt()->getIntoleranse()->har() ) {
                        continue;
                    }
                    $gruppe->addPerson($person);
                }
            }
        }

        return $gruppe;
    }
    
    /**
     * Lag og returner excel-filens URL
     * 
     * @return String url
     */
    public function getExcelFile()
    {
        $excel = new ExcelIntoleranser(
            $this->getNavn() . ' oppdatert ' . date('d-m-Y') . ' kl '. date('Hi') . ' - ' . $this->getArrangement()->getNavn(),
            $this->getRenderDataPersoner(),
            $this->getConfig()
        );
        return $excel->writeToFile();
    }
}