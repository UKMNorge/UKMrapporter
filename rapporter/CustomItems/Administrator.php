<?php

namespace UKMNorge\Rapporter\CustomItems;

use UKMNorge\Geografi\Kommune;
use UKMNorge\Nettverk\Administrator as NettverkAdministrator;
use UKMNorge\Rapporter\Framework\CustomItemInterface;

class Administrator implements CustomItemInterface {
    var $id;
    var $navn;
    var $mobil;
    var $epost;
    var $kommune;
    
    public function __construct( NettverkAdministrator $admin )
    {
        $this->id = intval($admin->getId());
        $this->navn = $admin->getNavn();
        $this->mobil = intval($admin->getUser()->getMobil());
        $this->epost = $admin->getUser()->getEpost();
    }

    /**
     * Hent administratorens ID
     *
     * @return Int id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Hent administratorens navn
     *
     * @return String
     */
    public function getNavn() {
        return $this->navn;
    }

    /**
     * Hent administratorens mobilnummer
     *
     * @return Int mobilnummer
     */
    public function getMobil() {
        return $this->mobil;
    }

    /**
     * Hent administratorens e-post
     *
     * @return String
     */
    public function getEpost() {
        return $this->epost;
    }

    /**
     * Sett administratorens kommune
     *
     * @param Kommune $kommune
     * @return self
     */
    public function setKommune( Kommune $kommune ) {
        $this->kommune = $kommune;
        return $this;
    }

    /**
     * Hent kommune-objekt
     *
     * @return Kommune
     */
    public function getKommune() {
        return $this->kommune;
    }
}