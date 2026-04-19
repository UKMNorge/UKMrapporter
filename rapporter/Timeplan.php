<?php

namespace UKMNorge\Rapporter;

use Exception;
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
        try {
            $arrangement = $this->getArrangement();
            if ($arrangement->getEierType() == 'fylke') {
                return [
                    'isFylkeArrangement' => true,
                    'alleKommuner' => $arrangement->getFylke()->getKommuner()->getAll()
                ];
            }
        } catch (Exception $e) {
            // Fallback for contexts where arrangement is unavailable.
        }

        return [
            'isFylkeArrangement' => false,
            'alleFylker' => Fylker::getAll()
        ];
    }
    
    public function getTemplate() {
        $selectedOmrader = [];
        $arrangement = $this->getArrangement();
        $filtrerPaKommuner = $arrangement->getEierType() == 'fylke';

        foreach($this->getConfig()->getAll() as $selectedDag) {
            $selectedOmrader[] = $selectedDag->getId();
        }

        $alleArrangementer = [];
        $alleHendelser = [];
        $omrader = [];

        foreach($arrangement->getProgram()->getAbsoluteAll() as $hendelse) {
            $hendelse->getInnslag()->getAll();
            foreach($hendelse->getInnslag()->getAll() as $innslag) {
                if ($filtrerPaKommuner) {
                    $skalViseInnslag = (count($selectedOmrader) == 0) ||
                        ($selectedOmrader[0] == 'vis_kommune_alle') ||
                        in_array('vis_kommune_' . $innslag->getKommune()->getId(), $selectedOmrader);
                }
                else {
                    $skalViseInnslag = (count($selectedOmrader) == 0) ||
                        ($selectedOmrader[0] == 'vis_fylke_alle') ||
                        in_array('vis_fylke_' . $innslag->getFylke()->getId(), $selectedOmrader);
                }

                if($skalViseInnslag) {

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
                    $omradeNavn = $filtrerPaKommuner ? $innslag->getKommune()->getNavn() : $innslag->getFylke()->getNavn();
                    $omrader[$fraArrangKey][$omradeNavn][$hendelse->getStart()->format('d.m.Y')][$hendelse->getId()][] = $innslag;
                }
            }
            
        }

        UKMrapporter::addViewData('omrader', $omrader);
        UKMrapporter::addViewData('isFylkeArrangement', $filtrerPaKommuner);
        UKMrapporter::addViewData('alleArrangementer', $alleArrangementer);
        UKMrapporter::addViewData('alleHendelser', $alleHendelser);

        return 'Timeplan/rapport.html.twig';
    }
}