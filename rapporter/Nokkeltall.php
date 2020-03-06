<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Innslag\Samling;
use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;

class Nokkeltall extends Rapport
{
    public $kategori_id = 'statistikk';
    public $ikon = 'dashicons-chart-pie';
    public $navn = 'Nøkkeltall';
    public $beskrivelse = 'Hvor mange påmeldte er det i hver kategori?';
    public $har_excel = false;
    public $har_sms = false;
    public $har_epost = false;
    

    public function getRenderData() {
        $grupper = new Gruppe('container', 'Nøkkeltall');

        $unike_personer = [];
        $antall_innslag = 0;
        $antall_personer = 0;

        $scene_innslag = 0;
        $scene_personer = 0;
        $scene_typer = [];

        foreach( $this->getArrangement()->getInnslagTyper(true)->getAll() as $innslag_type ) {
            $alle_innslag = Samling::filterByType( $innslag_type, $this->getArrangement()->getInnslag()->getAll());
            
            $personer = 0;
            foreach( $alle_innslag as $innslag ) {
                $antall_innslag ++;
                $personer += $innslag->getPersoner()->getAntall();
                foreach( $innslag->getPersoner()->getAll() as $person ) {
                    $antall_personer++;
                    $unike_personer[ $person->getId() ] = $person;
                }
            }

            if( $innslag_type->getId() == 1 ) {
                $scene_innslag += sizeof($alle_innslag);
                $scene_personer += $personer;
                $scene_typer[] = $innslag_type->getNavn();
            }

            $gruppe = new Gruppe( $innslag_type->getKey(), $innslag_type->getNavn() );
            $gruppe->setAttr('innslag_count', sizeof($alle_innslag));
            $gruppe->setAttr('personer_count', $personer);
            $grupper->addGruppe($gruppe);
        }

        $grupper->setAttr('personer_unike', sizeof($unike_personer));
        $grupper->setAttr('personer_count', $antall_personer);
        $grupper->setAttr('innslag_count', $antall_innslag);

        sort($scene_typer);
        $grupper->setAttr('scene_innslag_count', $scene_innslag);
        $grupper->setAttr('scene_personer_count', $scene_personer);
        $grupper->setAttr('scene_typer', $scene_typer);

        return $grupper;
    }

    /**
     * Hent hvilken template som skal benyttes
     *
     * @return String $template_id
     */
    public function getTemplate()
    {
        return 'Nokkeltall/rapport.html.twig';
    }
}