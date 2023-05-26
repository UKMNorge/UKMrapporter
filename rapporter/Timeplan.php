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
        $arrangement = $this->getArrangement();
        $dager = [];
        foreach($arrangement->getProgram()->getAbsoluteAll() as $hendelse) {
            foreach($hendelse->getInnslag()->getAll() as $innslag) {
                $dager[$hendelse->getOppmoteTid($innslag)->format('d.m.Y')] = $hendelse->getOppmoteTid($innslag)->format('d.m.Y');
            }
        }
        return ['dager' => $dager];
    }
    
    public function getTemplate() {
        $selectedDager = [];
        foreach($this->getConfig()->getAll() as $selectedDag) {
            $selectedDager[] = $selectedDag->getId();
        }

        $arrangement = $this->getArrangement();
        $dager = [];
        foreach($arrangement->getProgram()->getAbsoluteAll() as $hendelse) {
            $hendelse->getInnslag()->getAll();
            if((count($selectedDager) == 0) || ($selectedDager[0] == 'vis_dager_alle') || (in_array("vis_" . $hendelse->getStart()->format('d_m_Y'), $selectedDager))) {
                $dager[$hendelse->getStart()->format('d.m.Y')][$hendelse->getId()] = $hendelse;
            }
        }

        UKMrapporter::addViewData('dager', $dager);

        return 'Timeplan/rapport.html.twig';
    }
}