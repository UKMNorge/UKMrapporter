<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Innslag\Innslag;
use UKMNorge\Innslag\Samling;
use UKMNorge\Innslag\Typer\Typer;
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
    
    /**
     * Hent alle innslag som skal sorteres
     *
     * @return Array<Innslag>
     */
    public function getInnslag() {
        return $this->getArrangement()->getInnslag()->getAll();
    }

    /**
     * Hent alle innslagTyper vi skal vise
     *
     * @return Typer
     */
    public function getInnslagTyper() {
        return $this->getArrangement()->getInnslagTyper(true);
    }

    public function getRenderData() {
        $grupper = new Gruppe('container', 'Nøkkeltall');

        $unike_personer = [];
        $antall_innslag = 0;
        $antall_personer = 0;

        $scene_innslag = 0;
        $scene_personer = 0;
        $scene_typer = [];

        foreach( $this->getInnslagTyper()->getAll() as $innslag_type ) {
            $alle_innslag = Samling::filterByType( $innslag_type, $this->getInnslag());
            
            $personer = 0;
            foreach( $alle_innslag as $innslag ) {
                $antall_innslag ++;
                $personer += $innslag->getPersoner()->getAntall();
                foreach( $innslag->getPersoner()->getAll() as $person ) {
                    $antall_personer++;
                    $unike_personer[ $person->getNavn() . $person->getMobil() ] = $person;
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
        
        if($this->getConfig()->get('arrangement_type')) {
            $arrangementType = $this->getConfig()->get('arrangement_type')->getValue();
            $grupper->setAttr('arrangement_type', $arrangementType);
        }

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