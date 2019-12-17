<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;

class Program extends Rapport
{
    public $kategori_id = 'program';
    public $ikon = 'dashicons-editor-ol';
    public $navn = 'Program';
    public $beskrivelse = 'Program for dine hendelser';
    public $krever_hendelse = true;

    /**
     * Hent render-data for rapporten
     *
     * @return Gruppe
     */
    public function getRenderData()
    {
        $grupper = new Gruppe('container', 'Alle innslag');
        $grupper->setVisOverskrift(false);

        foreach( $this->getArrangement()->getProgram()->getAbsoluteAll() as $hendelse ) {
            if( !$this->getConfig()->vis('hendelse_'. $hendelse->getId()) ) {
                continue;
            }

            $gruppe_id = $hendelse->getNavn() . '-' . $hendelse->getId();
            if( !$grupper->harGruppe( $gruppe_id ) ) {
                $grupper->addGruppe(
                    new Gruppe(
                        $gruppe_id,
                        $hendelse->getNavn()
                    )
                );
            }
            
            $grupper->getGruppe( $gruppe_id )->setInnslag( $hendelse->getInnslag()->getAll());
        }
        return $grupper;
    }
}