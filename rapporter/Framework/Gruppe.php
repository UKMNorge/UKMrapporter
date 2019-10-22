<?php

namespace UKMNorge\Rapporter\Framework;
use UKMNorge\Innslag\Innslag;

class Gruppe
{

    var $id;
    var $overskrift;
    var $vis_overskrift = true;
    var $innslag = [];
    var $grupper = [];
    var $sorted_grupper = false;
    var $sorted_innslag = false;

    /**
     * Opprett en ny gruppe
     * Hvis gruppen er en undergruppe av en annen, er det 
     * spesielt viktig å angi en helt unik ID, som også 
     * kan brukes av ksort(). Anbefalt ID er Navn + ID.
     * 
     * @param String $id
     * @param String $overskrift
     */
    public function __construct( String $id, String $overskrift)
    {
        $this->id = $id;
        $this->overskrift = $overskrift;
    }

    /**
     * Hvorvidt denne gruppen skal ha, og har, innslag
     *
     * @return Bool $har_innslag
     */
    public function harInnslag()
    {
        return !$this->harGrupper() && sizeof($this->innslag) > 0;
    }

    /**
     * Hent alle innslag
     * @return Array<Innslag>
     */
    public function getInnslag()
    {
        if (!$this->sorted_innslag) {
            ksort($this->innslag);
        }
        return $this->innslag;
    }

    /**
     * Sett innslag som skal være med til view
     *
     * @param Array<Innslag>
     * @return self
     */
    public function setInnslag(array $innslag)
    {
        $this->innslag = $innslag;

        return $this;
    }

    /**
     * Legg til ett innslag som skal være med til view
     *
     * @param Innslag $innslag
     * @return self
     */
    public function addInnslag(Innslag $innslag)
    {
        $this->sorted_innslag = false;
        $this->innslag[$innslag->getNavn() . '-' . $innslag->getId()] = $innslag;
        return $this;
    }

    /**
     * Skal overskriften for denne gruppen vises?
     * @return Bool $vis_overskrift
     */
    public function visOverskrift()
    {
        return $this->vis_overskrift;
    }

    /**
     * Angi om gruppens overskrift skal vises
     *
     * @param Bool $vis_overskrift
     * @return  self
     */
    public function setVisOverskrift(Bool $vis_overskrift)
    {
        $this->vis_overskrift = $vis_overskrift;

        return $this;
    }

    /**
     * Hent gruppens overskrift
     * 
     * @return String $overskrift
     */
    public function getOverskrift()
    {
        return $this->overskrift;
    }

    /**
     * Har denne gruppen undergrupper?
     *
     * @return Bool
     */
    public function harGrupper()
    {
        return sizeof($this->grupper) > 0;
    }

    /**
     * Har gruppen en undergruppe med gitt overskrift?
     *
     * @param String $gruppe_overskrift
     * @return Bool
     */
    public function harGruppe(String $gruppe_overskrift)
    {
        return isset($this->grupper[$gruppe_overskrift]);
    }

    public function getGruppe(String $gruppe_overskrift)
    {
        return $this->grupper[$gruppe_overskrift];
    }
    /**
     * Hent undergrupper av denne gruppen
     */
    public function getGrupper()
    {
        if (!$this->sorted_grupper) {
            ksort($this->grupper);
            $this->sorted_grupper = true;
        }
        return $this->grupper;
    }

    /**
     * Legg til en undergruppe av denne gruppen
     *
     * @return  self
     */
    public function addGruppe(Gruppe $gruppe)
    {
        $this->sorted_grupper = false;
        $this->grupper[$gruppe->getId()] = $gruppe;

        return $this;
    }

    /**
     * Hent gruppens ID
     */ 
    public function getId()
    {
        return $this->id;
    }
}
