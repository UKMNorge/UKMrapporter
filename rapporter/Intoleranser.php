<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Excel\Intoleranser as ExcelIntoleranser;
use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Sensitivt\Requester;
use UKMNorge\Sensitivt\Sensitivt;
use UKMNorge\Arrangement\Videresending\Ledere\Ledere;
use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Geografi\Fylker;

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
        // if($this->getArrangement()->getType() == 'land') {
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

        $til = new Arrangement(get_option('pl_id'));
        foreach($til->getVideresending()->getAvsendere() as $avsender) {
            foreach(Fylker::getAll() as $fylke) {
                $fra = $avsender->getArrangement();
                if($fylke->getId() == $fra->getFylke()->getId()) {
                    $ledere = new Ledere($fra->getId(), $til->getId());
                    $ledereMed = [];
                    foreach($ledere->getAll() as $leder) {
                        if( !$leder->getSensitivt()->getIntoleranse()->har() ) {
                            continue;
                        }
                        
                        $leder->getSensitivt()->getIntoleranse();

                        $fylke_gruppe_id = $fylke->getNavn() . '-' . $fylke->getId();

                        if (!$gruppe->harGruppe($fylke_gruppe_id)) {
                            $gruppe->addGruppe(
                                new Gruppe(
                                    $fylke_gruppe_id,
                                    $fylke->getNavn()
                                )
                            );
                        }
                        $leder->setNavn($leder->getNavn() . ' ('. $leder->getTypeNavn() .')');
                        $ledereMed[] = $leder;
                    }
                    // Merger personer og ledere. Dette kan feile og her brukes kun som representasjon på brukergrensesnitt. Person og Leder er veldig forskjellige men metodene som kalles på GUI i dette tilfelle finnes i begge klassene
                    if($gruppe->getGruppe($fylke_gruppe_id)) {
                        $personerLedere = array_merge($gruppe->getGruppe($fylke_gruppe_id)->getPersoner(), $ledereMed);
                        $gruppe->getGruppe($fylke_gruppe_id)->setPersoner($personerLedere);
                    }
                }
            }
        }
        // }
        // // Alle andre arrangementer
        // else {
        //     foreach( $this->getArrangement()->getInnslag()->getAll() as $innslag ) {
        //         foreach( $innslag->getPersoner()->getAll() as $person ) {
        //             if( !$person->getSensitivt()->getIntoleranse()->har() ) {
        //                 continue;
        //             }
        //             $gruppe->addPerson($person);
        //         }
        //     }
        // }

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