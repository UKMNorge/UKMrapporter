<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Geografi\Fylker;


class AlleInnslag extends Rapport
{
    public $kategori_id = 'personer';
    public $ikon = 'dashicons-buddicons-buddypress-logo';
    public $navn = 'Alle innslag';
    public $beskrivelse = 'Informasjon om alle som er påmeldt arrangementet.';
    public $har_word = true;


    /**
     * Data til "tilpass rapporten"
     * 
     * @return Array
     */
    public function getCustomizerData() {
        return ['alleFylker' => Fylker::getAll()];
    }

    /**
     * Hent render-data for rapporten
     *
     * @return Gruppe
     */
    public function getRenderData()
    {
        $grupper = new Gruppe('container', 'Alle innslag');
        $grupper->setVisOverskrift(false);

        switch ($this->getConfig()->get('grupper')) {
                // Gruppér alle innslag etter type
            case 'type':
                foreach ($this->getArrangement()->getInnslag()->getAll() as $innslag) {
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
                break;

                // Gruppér alle innslag etter type og kommune
            case 'type_kommune':
                foreach ($this->getArrangement()->getInnslag()->getAll() as $innslag) {
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

                    // Har denne typen innslag fra denne kommunen?
                    $kommune_gruppe_id = $innslag->getKommune()->getNavn() . '-' . $innslag->getKommune()->getId();
                    if (!$grupper->getGruppe($type_gruppe_id)->harGruppe($kommune_gruppe_id)) {
                        $grupper->getGruppe($type_gruppe_id)->addGruppe(
                            new Gruppe(
                                $kommune_gruppe_id,
                                $innslag->getKommune()->getNavn()
                            )
                        );
                    }

                    // Legg til innslag i undergruppen
                    $grupper->getGruppe($type_gruppe_id)->getGruppe($kommune_gruppe_id)->addInnslag($innslag);
                }
                break;
            case 'kommune':
                foreach ($this->getArrangement()->getInnslag()->getAll() as $innslag) {

                    // Har denne typen innslag fra denne kommunen?
                    $kommune_gruppe_id = $innslag->getKommune()->getNavn() . '-' . $innslag->getKommune()->getId();
                    if (!$grupper->harGruppe($kommune_gruppe_id)) {
                        $grupper->addGruppe(
                            new Gruppe(
                                $kommune_gruppe_id,
                                $innslag->getKommune()->getNavn()
                            )
                        );
                    }

                    $grupper->getGruppe($kommune_gruppe_id)->addInnslag($innslag);
                }
                    break;

            case 'kommune_type':
                foreach ($this->getArrangement()->getInnslag()->getAll() as $innslag) {

                    // Opprett kommune-gruppe om den ikke finnes
                    $kommune_gruppe_id = $innslag->getKommune()->getNavn() . '-' . $innslag->getKommune()->getId();
                    if (!$grupper->harGruppe($kommune_gruppe_id)) {
                        $grupper->addGruppe(
                            new Gruppe(
                                $kommune_gruppe_id,
                                $innslag->getKommune()->getNavn()
                            )
                        );
                    }

                    // Har denne kommunen denne typen innslag?
                    $type_gruppe_id = $innslag->getType()->getNavn();
                    if (!$grupper->getGruppe($kommune_gruppe_id)->harGruppe($type_gruppe_id)) {
                        $grupper->getGruppe($kommune_gruppe_id)->addGruppe(
                            new Gruppe(
                                $type_gruppe_id,
                                $innslag->getType()->getNavn()
                            )
                        );
                    }

                    $grupper->getGruppe($kommune_gruppe_id)->getGruppe($type_gruppe_id)->addInnslag($innslag);
                }
                break;

                // Gruppér alle innslag etter fylke
                case 'fylke_type':

                    $fylker = [];

                    foreach($this->getConfig()->getAll() as $selectedItem) {
                        $id = $selectedItem->getId();

                        // se under, hvis fylker length er 0, blir alle fylkene med
                        if($id == 'vis_fylkeshow_alle') {
                            $fylker = [];
                            break;
                        }

                        if(strpos($id, 'vis_fylkeshow') !== false) {
                            $fylkeId = explode("_", $id, 3)[2];
                            $fylker[$fylkeId] = Fylker::getById($fylkeId);
                        }
                    }

                    // Alle fylker eller ingen ble valgt. Likevel ble alle fylker eller blir resulat ingen ting!
                    if(count($fylker) < 1) {
                        foreach(Fylker::getAll() as $fylke) {
                            $fylker[$fylke->getId()] = $fylke;
                        }
                    }

                    foreach ($this->getArrangement()->getInnslag()->getAll() as $innslag) {
                        $fylkeId = $innslag->getFylke()->getId();

                        // Hvis fylke er velgt
                        if(array_key_exists($fylkeId, $fylker)) {
                            // Opprett fylke-gruppe om den ikke finnes
                            $fylke_gruppe_id = $innslag->getFylke()->getNavn() . '-' . $fylkeId;
                            if (!$grupper->harGruppe($fylke_gruppe_id)) {
                                $grupper->addGruppe(
                                    new Gruppe(
                                        $fylke_gruppe_id,
                                        $innslag->getFylke()->getNavn()
                                    )
                                );
                            }
        
                            // Har dette fylket denne typen innslag?
                            $type_gruppe_id = $innslag->getType()->getNavn();
                            if (!$grupper->getGruppe($fylke_gruppe_id)->harGruppe($type_gruppe_id)) {
                                $grupper->getGruppe($fylke_gruppe_id)->addGruppe(
                                    new Gruppe(
                                        $type_gruppe_id,
                                        $innslag->getType()->getNavn()
                                    )
                                );
                            }
        
                            $grupper->getGruppe($fylke_gruppe_id)->getGruppe($type_gruppe_id)->addInnslag($innslag);
                        }

                    }
                    break;

                // Vis alle innslag alfabetisk
            case 'alfabetisk':
            default:
                $grupper->setInnslag($this->getArrangement()->getInnslag()->getAll());
                break;
        }

        return $grupper;
    }
}