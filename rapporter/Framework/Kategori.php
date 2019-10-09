<?php

namespace UKMNorge\Rapporter\Framework;

class Kategori
{
    var $id = null;
    var $navn;
    var $ikon;
    var $rapporter = [];

    /**
     * Opprett nytt kategori-objekt
     *
     * @param stdClass $data
     */
    public function __construct(String $id, array $data)
    {
        $this->id = $id;
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Hent kategori-ID
     * 
     * @return String $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Hent kategori-navn
     * 
     * @return String $navn
     */
    public function getNavn()
    {
        return $this->navn;
    }

    /**
     * Legg til rapport i kategorien
     *
     * @param Rapport $rapport
     * @return self
     */
    public function add($rapport)
    {
        $this->rapporter[$rapport->getId()] = $rapport;
        return $this;
    }

    /**
     * Hent alle rapporter i kategorien
     *
     * @return Array<Rapport>
     */
    public function getRapporter()
    {
        return $this->rapporter;
    }

    /**
     * Har kategorien noen som helst rappporter?
     *
     * @return Bool
     */
    public function harRapporter()
    {
        return sizeof($this->rapporter) > 0;
    }
}
