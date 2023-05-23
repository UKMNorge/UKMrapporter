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
    public function getCustomizerData() {
        return ['alleFylker' => Fylker::getAll()];
    }

    
    public function getTemplate() {
        $arrangement = $this->getArrangement();
        $hendelser = [];
        foreach($arrangement->getProgram()->getAll() as $hendelse) {
            $hendelse->getInnslag()->getAll();
            $hendelse->getTid();
            $hendelser[] = $hendelse;
        }


        

        UKMrapporter::addViewData('sorteringMetode', -1);
        UKMrapporter::addViewData('hendelser', $hendelser);

        return 'Timeplan/rapport.html.twig';
    }
}