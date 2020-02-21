<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;

class Personvern extends Program
{
    public $kategori_id = 'personer';
    public $ikon = 'dashicons-welcome-view-site';
    public $navn = 'Personvern';
    public $beskrivelse = 'Alle deltakere som ikke vil bli filmet eller tatt bilde av';
    public $krever_hendelse = false;

    public function getRenderData()
    {
        $grupper = new Gruppe('container', 'Alle innslag');
        $grupper->setVisOverskrift(false);

        if( $this->config->show("gruppert_for_hendelse")) {
            foreach( $this->getArrangement()->getProgram()->getAbsoluteAll() as $hendelse ) {
                if( !$this->getConfig()->vis('hendelse_'. $hendelse->getId()) ) {
                    continue;
                }

                # Lag en gruppe per forestilling
                $f_gruppe_id = $hendelse->getNavn() . '-' . $hendelse->getId();
                $f_gruppe = null;
                if( !$grupper->harGruppe( $f_gruppe_id ) ) {
                    $f_gruppe = new Gruppe(
                        $f_gruppe_id,
                        $hendelse->getNavn()
                    );
                    $grupper->addGruppe( $f_gruppe );
                }

                # For alle innslag i hendelsen, se om innslaget har deltakere som har reservert seg mot film og foto
                foreach( $hendelse->getInnslag()->getAll() as $innslag ) {
                    if( !$innslag->getSamtykke()->harNei() ) {
                        continue;
                    }
                    # Legg til innslag i forestillingsgruppe
                    $gruppe_id = $innslag->getNavn() . '-' . $innslag->getId();
                    if( !$f_gruppe->harGruppe( $gruppe_id ) ) {
                        $f_gruppe->addGruppe(
                            new Gruppe(
                                $gruppe_id,
                                $innslag->getNavn()
                            )
                        );
                    }
                    $f_gruppe->getGruppe( $gruppe_id )->setInnslag( [ $innslag ] );
                }
            }
        } else {
            foreach( $this->getArrangement()->getInnslag()->getAll() as $innslag ) {

                # Se om innslaget har deltakere som har reservert seg mot film og foto.
                # Hvis ikke hopper vi bare over det.
                if( !$innslag->getSamtykke()->harNei() ) {
                    continue;
                }

                # Lag en gruppe per innslag.
                $gruppe_id = $innslag->getNavn() . '-' . $innslag->getId();
                if( !$grupper->harGruppe( $gruppe_id ) ) {
                    $grupper->addGruppe(
                        new Gruppe(
                            $gruppe_id,
                            $innslag->getNavn()
                        )
                    );
                }
                
                $grupper->getGruppe( $gruppe_id )->setInnslag( [ $innslag ] );
            }    
        }
        
        return $grupper;
    }
    
}