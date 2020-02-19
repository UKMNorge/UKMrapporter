<?php

namespace UKMNorge\Rapporter\Framework;

use UKMNorge\Innslag\Innslag;
use UKMNorge\Innslag\Personer\Person;

class Gruppe
{

    var $id;
    var $overskrift;
    var $vis_overskrift = true;
    var $grupper = [];
    var $innslag = [];
    var $personer = [];
    var $sorted_grupper = false;
    var $sorted_innslag = false;
    var $sorted_personer = false;
    var $attributes = [];
    
    /**
     * Opprett en ny gruppe
     * Hvis gruppen er en undergruppe av en annen, er det 
     * spesielt viktig å angi en helt unik ID, som også 
     * kan brukes av ksort(). Anbefalt ID er Navn + ID.
     * 
     * harInnslag() brukes før harPersoner(), som vil si at hvis både
     * personer og innslag er lagt til, vil gruppen ved visning kun rendre ut innslagene
     * 
     * @param String $id
     * @param String $overskrift
     */
    public function __construct(String $id, String $overskrift)
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
     * Hvorvidt denne gruppen skal ha, og har, personer
     *
     * @return Bool $har_innslag
     */
    public function harPersoner()
    {
        return !$this->harGrupper() && sizeof($this->personer) > 0;
    }

    /**
     * Hent alle personer (key-sorted)
     * @return Array<Person>
     */
    public function getPersoner()
    {
        if (!$this->sorted_personer) {
            ksort($this->personer);
        }
        return $this->personer;
    }

    /**
     * Sett personer som skal være med til view
     * 
     * OBS: Overskriver personer lagt til tidligere
     *
     * @param Array<Person>
     * @return self
     */
    public function setPersoner(array $personer)
    {
        $this->personer = $personer;

        return $this;
    }

    /**
     * Legg til en person som skal være med til view
     *
     * @param Person $person
     * @return self
     */
    public function addPerson(Person $person)
    {
        $this->sorted_innslag = false;
        $this->personer[$person->getNavn() . '-' . $person->getId()] = $person;
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

    /**
     * Sett attributt
     *
     * @param string $key
     * @param $value
     *
     * @return innslag
     **/
    public function setAttr($key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Hent attributt
     *
     * @param string $key
     *
     * @return value
     **/
    public function getAttr($key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : false;
    }
}
