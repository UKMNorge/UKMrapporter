<?php

namespace UKMNorge\Rapporter\Framework;

abstract class Rapport
{
    public $id;
    public $navn;
    public $kategori_id;
    public $beskrivelse;
    public $ikon;

    /**
     * Hent rapport-ID
     * 
     * @return String $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Hent rapport-navn
     * 
     * @return String $navn
     */
    public function getNavn()
    {
        return $this->navn;
    }

    /**
     * Hent rapportens kategori
     * 
     * @return String $kategori
     */
    public function getKategori()
    {
        return Kategorier::getById($this->kategori_id);
    }

    /**
     * Hent beskrivelsen
     * 
     * @return String $beskrivelse
     */
    public function getBeskrivelse()
    {
        return $this->beskrivelse;
    }

    /**
     * Hent rapportens ikon
     * 
     * @return String $dashicon-ID
     */
    public function getIkon()
    {
        return $this->ikon;
    }

    public function supportExcel()
    {
        return method_exists($this, 'renderExcel');
    }
    public function supportWord()
    {
        return method_exists($this, 'renderWord');
    }
}
