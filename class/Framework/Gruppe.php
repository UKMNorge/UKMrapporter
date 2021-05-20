<?php

namespace UKMNorge\Rapporter\Framework;

use UKMNorge\Innslag\Innslag;
use UKMNorge\Innslag\Personer\Person;
use UKMNorge\Innslag\Typer\Type;
use UKMNorge\Innslag\Nominasjon\Nominasjon;


class Gruppe
{

    var $id;
    var $overskrift;
    var $vis_overskrift = true;
    var $grupper = [];
    var $innslag = [];
    var $personer = [];
    var $nominasjoner = [];
    var $custom_items = [];
    var $sorted_data = false;
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
        if (!$this->sorted_data) {
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
        $this->sorted_data = false;
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
        $this->sorted_data = false;
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
        if (!$this->sorted_data) {
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
     * Funksjonen passer automatisk på at én unik person blir med i rapporten
     * maks én gang.
     *
     * @param Person $person
     * @return self
     */
    public function addPerson(Person $person)
    {
        $this->sorted_data = false;
        $this->personer[$person->getNavn() . '-' . $person->getId()] = $person;
        return $this;
    }

    /**
     * Legg til en nominasjon
     *
     * @param Nominasjon $nominasjon
     * @return self
     */
    public function addNominasjon(Nominasjon $nominasjon ) 
    {
        $this->nominasjoner[] = $nominasjon;

        return $this;
    }

    /**
     * Hent alle nominasjoner
     * @return Array<Nominasjon>
     */
    public function getNominasjoner()
    {
        return $this->nominasjoner;
    }

    /**
     * Hvorvidt denne gruppen skal ha, og har, nominasjoner
     *
     * @return Bool
     */
    public function harNominasjoner()
    {
        return !$this->harGrupper() && sizeof($this->nominasjoner) > 0;
    }    

    /**
     * Hvorvidt denne gruppen skal ha, og har, custom-items
     *
     * @return Bool $har_custom_item
     */
    public function harCustomItems()
    {
        return !$this->harGrupper() && sizeof($this->custom_items) > 0;
    }

    /**
     * Hent alle items (key-sorted)
     * @return Array<CustomItemInterface>
     */
    public function getCustomItems()
    {
        if (!$this->sorted_data) {
            ksort($this->custom_items);
        }
        return $this->custom_items;
    }

    /**
     * Sett custom items som skal være med til view
     * 
     * OBS: Overskriver personer lagt til tidligere
     *
     * @param Array<CustomItemInterface>
     * @return self
     */
    public function setCustomItems(array $custom_items)
    {
        foreach($custom_items as $custom_item ) {
            $this->addCustomItem($custom_item);
        }

        return $this;
    }

    /**
     * Legg til en custom item som skal være med til view
     * 
     * Funksjonen passer automatisk på at et unikt objekt blir med i rapporten
     * maks én gang.
     *
     * @param CustomItemInterface item
     * @return self
     */
    public function addCustomItem(CustomItemInterface $custom_item)
    {
        $this->sorted_data = false;
        $this->custom_items[$custom_item->getId()] = $custom_item;
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
     * Har gruppen en undergruppe med gitt id?
     *
     * @param String $gruppe_id
     * @return Bool
     */
    public function harGruppe(String $gruppe_id)
    {
        return isset($this->grupper[$gruppe_id]);
    }

    /**
     * Hent en gruppe fra id
     *
     * @param String $gruppe_id
     * @return Gruppe
     */
    public function getGruppe(String $gruppe_id)
    {
        return $this->grupper[$gruppe_id];
    }

    /**
     * Hent undergrupper av denne gruppen
     * 
     * @return Array<Gruppe>
     */
    public function getGrupper()
    {
        if (!$this->sorted_data) {
            ksort($this->grupper);
            $this->sorted_data = true;
        }
        return $this->grupper;
    }

    /**
     * Legg til en undergruppe av denne gruppen
     *
     * @param Gruppe $gruppe
     * @return self
     */
    public function addGruppe(Gruppe $gruppe)
    {
        $this->sorted_data = false;
        $this->grupper[$gruppe->getId()] = $gruppe;

        return $this;
    }

    /**
     * Hent gruppens ID
     * 
     * @return Int id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sett attributt
     *
     * @param String $key
     * @param mixed $value
     *
     * @return self
     **/
    public function setAttr(String $key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Hent attributt
     *
     * @param String $key
     *
     * @return mixed
     **/
    public function getAttr(String $key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : false;
    }
}
