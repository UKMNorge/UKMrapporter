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
    public function getCustomizerData()
    {
        return ['alleFylker' => Fylker::getAll()];
    }
    
    public function getTemplate() {
        $selectedFylker = [];
        foreach($this->getConfig()->getAll() as $selectedDag) {
            $selectedFylker[] = $selectedDag->getId();
        }
        $alleArrangementer = [];
        $alleHendelser = [];
        $arrangement = $this->getArrangement();
        $fylker = [];

        foreach($arrangement->getProgram()->getAbsoluteAll() as $hendelse) {
            $hendelse->getInnslag()->getAll();
            foreach($hendelse->getInnslag()->getAll() as $innslag) {
                if((count($selectedFylker) == 0) || ($selectedFylker[0] == 'vis_fylke_alle') || (in_array("vis_fylke_" . $innslag->getFylke()->getId(), $selectedFylker))) {

                    $fraArrangKey = 0; 

                    // OBS: her brukes fylke id fordi 2 fylker hadde flere arrangementer og det var ønskelig å sortere etter arrangementer. Dette er kun en quick fix og ikke en løsning!
                    if($innslag->getFylke()->getId() == 30 || $innslag->getFylke()->getId() == 54) {
                        $fraArrangement = $arrangement->getVideresendingArrangement($innslag->getId());
                        if($fraArrangement) {
                            $fraArrangKey = $fraArrangement->getId();
                            $alleArrangementer[$fraArrangement->getId()] = $fraArrangement;
                        }
                    }
                    
                    $alleHendelser[$hendelse->getId()] = $hendelse;
                    $fylker[$fraArrangKey][$innslag->getFylke()->getNavn()][$hendelse->getStart()->format('d.m.Y')][$hendelse->getId()][] = $innslag;
                }
            }
            
        }

        UKMrapporter::addViewData('fylker', $fylker);
        UKMrapporter::addViewData('alleArrangementer', $alleArrangementer);
        UKMrapporter::addViewData('alleHendelser', $alleHendelser);

        return 'Timeplan/rapport.html.twig';
    }
}