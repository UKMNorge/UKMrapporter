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
        $hendelser = [];
        $dager = [];
        foreach($arrangement->getProgram()->getAbsoluteAll() as $hendelse) {
            $hendelse->getInnslag()->getAll();
            $hendelse->getTid();
            $hendelser[] = $hendelse;
            foreach($hendelse->getInnslag()->getAll() as $innslag) {
                // Hvis ingen dag selektert vis alle dager eller hvis alle dager selektert vis alle eller sjekk dag og legg til hvis dag er selektert
                if((count($selectedDager) == 0) || ($selectedDager[0] == 'vis_dager_alle') || (in_array("vis_" . $hendelse->getOppmoteTid($innslag)->format('d_m_Y'), $selectedDager))) {
                    $dager[$hendelse->getOppmoteTid($innslag)->format('d.m.Y')]['hendelse'][$hendelse->getId()] = $hendelse;
                    $dager[$hendelse->getOppmoteTid($innslag)->format('d.m.Y')]['innslag'][] = $innslag;
                }
            }
        }

        UKMrapporter::addViewData('sorteringMetode', -1);
        UKMrapporter::addViewData('hendelser', $hendelser);
        UKMrapporter::addViewData('dager', $dager);

        return 'Timeplan/rapport.html.twig';
    }
}