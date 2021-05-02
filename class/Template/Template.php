<?php

namespace UKMNorge\Rapporter\Template;

class Template {
    var $id;
    var $name;
    var $user_id;
    var $rapport_id;
    var $arrangement_id;
    var $config;
    var $beskrivelse;

    /**
     * Opprett template-objekt fra data
     *
     * @param Array $data
     */
    public function __construct( Array $data ) {
        $this->id = (Int) $data['id'];
        $this->name = $data['name'];
        $this->user_id = (Int) $data['user_id'];
        $this->rapport_id = $data['report_id'];
        $this->arrangement_id = (Int) $data['pl_id'];
        $this->omrade_id = $data['omrade_id'];
        $this->omrade_type = $data['omrade_type'];
        $this->beskrivelse = $data['description'];
        if( isset( $data['config'] ) ) {
            $this->config = json_decode( $data['config'] );
        }
    }

    /**
     * Hent templateID
     * 
     * @return Int $id;
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sett templateID
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Hent template-navn
     * 
     * @return String $name
     */ 
    public function getNavn()
    {
        return $this->name;
    }

    /**
     * Sett template-navn
     *
     * @param String $name
     * @return  self
     */ 
    public function setNavn($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Hent bruker-ID (wordpress)
     * 
     * @return Int $user_id
     */ 
    public function getBrukerId()
    {
        return $this->user_id;
    }

    /**
     * Hent arrangement-ID
     * 
     * @return Int $arrangement_id
     */ 
    public function getArrangementId()
    {
        return $this->arrangement_id;
    }

    /**
     * Hent område ID
     * 
     * @return Int $arrangement_id
     */ 
    public function getOmradeId()
    {
        return $this->omrade_id;
    }

    /**
     * Hent område type
     * 
     * @return string $arrangement_id
     */ 
    public function getOmradeType()
    {
        return $this->omrade_type;
    }

    /**
     * Hent rapport-konfigurasjon
     * 
     * @return Array $config
     */ 
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Hent rapport-konfigurasjon som JSON-data
     * 
     * @return String JSON $config
     */ 
    public function getConfigString()
    {
        return json_encode($this->config);
    }

    /**
     * Sett rapport-konfigurasjon
     *
     * @param Array $config
     * @return  self
     */ 
    public function setConfig( Array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Hent rapport-ID 
     * Hvilken rapport template'n gjelder for
     * 
     * @return String $rapport_id
     */ 
    public function getRapportId()
    {
        return $this->rapport_id;
    }

    /**
     * Sett rapport-ID
     *
     * @param String $rapport_id
     * @return  self
     */ 
    public function setRapportId(String $rapport_id)
    {
        $this->rapport_id = $rapport_id;

        return $this;
    }

    /**
     * Hent brukerens template-beskrivelse
     * 
     * @return String $beskrivelse
     */ 
    public function getBeskrivelse()
    {
        return $this->beskrivelse;
    }

    /**
     * Sett brukerens template-beskrivelse
     *
     * @param String $beskrivelse
     * @return  self
     */ 
    public function setBeskrivelse( String $beskrivelse)
    {
        $this->beskrivelse = $beskrivelse;

        return $this;
    }
}