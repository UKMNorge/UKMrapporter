<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Excel\Intoleranser as ExcelIntoleranser;
use UKMNorge\Rapporter\Framework\ConfigValue;
use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Rapporter\Word\FormatterDiplom;

class Diplom extends Rapport
{
    public $kategori_id = 'personer';
    public $ikon = 'dashicons-welcome-learn-more';
    public $navn = 'Diplomer';
    public $beskrivelse = 'Last ned ferdig PDF klar for utskrift.';
    public $krever_hendelse = false;
    public $har_word = false;
    public $har_excel = false;
    public $har_sms = false;
    public $har_epost = false;

    public function getTemplate()
    {
        return 'Diplom/rapport.html.twig';
    }

    public function getRenderData()
    {
        $this->getConfig()->add(new ConfigValue("vis_deltakere", "true"));
        $gruppe = new Gruppe('container', '');
        $gruppe->setVisOverskrift(false);

        foreach ($this->getArrangement()->getInnslag()->getAll() as $innslag) {
            foreach ($innslag->getPersoner()->getAll() as $person) {
                $gruppe->addPerson($person);
            }
        }
        return $gruppe;
    }

    /**
     * Data to make the diplomas preview render names client-side.
     */
    public function getCustomizerData()
    {
        $personer = [];

        foreach ($this->getArrangement()->getInnslag()->getAll() as $innslag) {
            foreach ($innslag->getPersoner()->getAll() as $person) {
                $personer[] = [
                    'id' => $person->getId(),
                    'navn' => $person->getNavn(),
                    'innslag' => $innslag->getNavn(),
                    'type' => $innslag->getType()->getNavn(),
                ];
            }
        }

        return [
            'diplomPersoner' => $personer,
        ];
    }

    /**
     * Hent spesifikk wordFormatter
     * 
     * @return WordFormatter
     */
    public function getWordFormatter()
    {
        $this->getConfig()->add(
            new ConfigValue(
                'arrangement_navn',
                $this->getArrangement()->getNavn()
            )
        );

        return new FormatterDiplom($this->getConfig());
    }
}
