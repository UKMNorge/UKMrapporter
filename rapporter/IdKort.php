<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Rapporter\Framework\Config;
use UKMNorge\Rapporter\Framework\ConfigValue;
use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Rapporter\Word\FormatterDiplom;

class IdKort extends Rapport
{
    public $kategori_id = 'personer';
    public $ikon = 'dashicons-welcome-learn-more';
    public $navn = 'ID-Kort';
    public $beskrivelse = 'Last ned ferdig PDF klar for utskrift.';
    public $krever_hendelse = true;
    public $har_word = false;
    public $har_excel = false;
    public $har_sms = false;
    public $har_epost = false;

    public function getTemplate()
    {
        return 'IdKort/rapport.html.twig';
    }

    public function getRenderData()
    {
        $gruppe = new Gruppe('container', '');
        $gruppe->setVisOverskrift(false);

        foreach ($this->getPersonerPerHendelse($this->getValgteHendelser(), $this->getConfig()) as $personData) {
            if (isset($personData['person'])) {
                $gruppe->addPerson($personData['person']);
            }
        }
        return $gruppe;
    }

    /**
     * Data to make the diplomas preview render names client-side.
     */
    public function getCustomizerData()
    {
        $config = $this->getConfig();
        if (!$config) {
            $config = new Config();
        }

        $hendelserData = [];
        $personer = [];

        foreach ($this->getArrangement()->getProgram()->getAbsoluteAll() as $hendelse) {
            $rolle = $this->getRolleForHendelse($config, $hendelse->getId());

            if ($config->vis('hendelse_' . $hendelse->getId())) {
                foreach ($this->getUnikePersonerForHendelse($hendelse) as $person) {
                    $personer[] = [
                        'id' => $hendelse->getId() . '-' . $person->getId(),
                        'navn' => $person->getNavn(),
                        'fornavn' => method_exists($person, 'getFornavn') ? trim((string) $person->getFornavn()) : '',
                        'rolle' => $rolle,
                        'hendelseId' => $hendelse->getId(),
                        'hendelseNavn' => $hendelse->getNavn(),
                    ];
                }
            }

            $hendelserData[] = [
                'id' => $hendelse->getId(),
                'navn' => $hendelse->getNavn(),
                'rolle' => $rolle,
            ];
        }

        return [
            'idkortPersoner' => $personer,
            'idkortHendelser' => $hendelserData,
        ];
    }

    /**
     * Hent valgte programhendelser fra config
     *
     * @return array
     */
    protected function getValgteHendelser()
    {
        $hendelser = [];
        $config = $this->getConfig();

        if (!$config) {
            return $hendelser;
        }

        foreach ($this->getArrangement()->getProgram()->getAbsoluteAll() as $hendelse) {
            if (!$config->vis('hendelse_' . $hendelse->getId())) {
                continue;
            }

            $hendelser[] = $hendelse;
        }

        return $hendelser;
    }

    /**
     * Samle unike personer fra valgte hendelser
     *
     * @param array $hendelser
     * @return array
     */
    protected function getPersonerPerHendelse($hendelser, $config)
    {
        $personer = [];

        if (!$config) {
            $config = new Config();
        }

        foreach ($hendelser as $hendelse) {
            $unikePersoner = $this->getUnikePersonerForHendelse($hendelse);
            foreach ($unikePersoner as $person) {
                $personer[] = [
                    'person' => $person,
                    'hendelseId' => $hendelse->getId(),
                    'hendelseNavn' => $hendelse->getNavn(),
                    'rolle' => $this->getRolleForHendelse($config, $hendelse->getId()),
                ];
            }
        }

        return $personer;
    }

    /**
     * Samle unike personer for en hendelse
     *
     * @param object $hendelse
     * @return array
     */
    protected function getUnikePersonerForHendelse($hendelse)
    {
        $personer = [];

        foreach ($hendelse->getInnslag()->getAll() as $innslag) {
            foreach ($innslag->getPersoner()->getAll() as $person) {
                $personer[$person->getId()] = $person;
            }
        }

        return array_values($personer);
    }

    /**
     * Hent rolleverdi for person i hendelse
     *
     * @param Config $config
     * @param int $hendelseId
     * @param object $person
     * @return string
     */
    protected function getRolleForHendelse($config, $hendelseId)
    {
        $key = 'rolle_' . $hendelseId;
        if ($config && $config->har($key)) {
            return trim($config->get($key)->getValue());
        }

        return '';
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
