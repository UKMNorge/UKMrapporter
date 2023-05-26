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

        $arrangement = $this->getArrangement();
        $fylker = [];
        foreach($arrangement->getProgram()->getAbsoluteAll() as $hendelse) {
            $hendelse->getInnslag()->getAll();
            foreach($hendelse->getInnslag()->getAll() as $innslag) {
                if((count($selectedFylker) == 0) || ($selectedFylker[0] == 'vis_dager_alle') || (in_array("vis_fylke_" . $innslag->getFylke()->getId(), $selectedFylker))) {
                    $fylker[$innslag->getFylke()->getNavn()][$hendelse->getStart()->format('U')][$hendelse->getId()] = $hendelse;
                }
            }
            
        }

        UKMrapporter::addViewData('fylker', $fylker);

        return 'Timeplan/rapport.html.twig';
    }
}