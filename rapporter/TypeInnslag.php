<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;

class TypeInnslag extends AlleInnslag
{
    public $kategori_id = 'personer';
    public $ikon = 'dashicons-translation';
    public $navn = 'Type innslag';
    public $beskrivelse = 'Velg hvilken type deltakere du vil ha informasjon om';
    public $har_word = true;

    /**
     * Hent render-data for rapporten
     *
     * @return Gruppe
     */
    public function getRenderData()
    {
        $grupper = new Gruppe('container', 'Alle innslag');
        $grupper->setVisOverskrift(false);

        foreach (  $this->getArrangement()->getInnslag()->getAll() as $innslag) {
            if( !$this->getConfig()->vis('type_'.$innslag->getType()->getKey())) {
                continue;
            }
            $type_gruppe_id = $innslag->getType()->getNavn();
            // Opprett gruppe om den ikke finnes
            if (!$grupper->harGruppe($type_gruppe_id)) {
                $grupper->addGruppe(
                    new Gruppe(
                        $type_gruppe_id,
                        $innslag->getType()->getNavn()
                    )
                );
            }
            // Legg til innslag
            $grupper->getGruppe($type_gruppe_id)->addInnslag($innslag);
        }
        return $grupper;
    }
}