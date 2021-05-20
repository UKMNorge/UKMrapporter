<?php

namespace UKMNorge\Rapporter;

use Exception;
use UKMrapporter;
use UKMNorge\Geografi\Kommune;
use UKMNorge\Nettverk\Administrator as NetverkAdministrator;
use UKMNorge\Nettverk\Omrade;
use UKMNorge\Rapporter\Framework\ConfigValue;
use UKMNorge\Rapporter\Framework\Rapport;
use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Word\FormatterNominasjoner;
use UKMNorge\Innslag\Personer\Person;





class Nominasjoner extends Rapport
{
    public $kategori_id = 'personer';
    public $ikon = 'dashicons-yes-alt';
    public $navn = 'Nominasjoner';
    public $beskrivelse = 'Alle nominsasjoner';
    public $har_excel = false;
    public $har_word = true;
    public $har_epost = false;
    public $har_sms = false;
    public $arrangementer = [];


    public function getRenderData() {
        $this->getConfig()->add(new ConfigValue("vis_deltakere", "true"));
        $gruppe = new Gruppe('container', '');
        $gruppe->setVisOverskrift(false);

        $til = new Arrangement(get_option('pl_id'));
        $avsenderArrangementer = $til->getVideresending()->getAvsendere();

        $arrTyper = $til->getInnslagTyper();
        $arrTyper =  array_filter( $arrTyper->getAll() , function($type) use ($til) { return $type->kanHaNominasjon() && $til->harNominasjonFor($type); });
            
        foreach($arrTyper as $type) {
            foreach($avsenderArrangementer as $fra) {
        		foreach($fra->getInnslag()->getAllByType($type) as $innslag) {
                    
                    $nominert = $innslag->getNominasjoner()->harTil($til->getId());
                    $nominasjon = $nominert ? $innslag->getNominasjoner()->getTil($til->getId()) : false;
                    $person = $innslag->getPersoner()->getSingle();

                    if($nominasjon->erNominert()) {
                        $gruppe->addNominasjon($nominasjon);
                    }
                }
            }
        }
        
        return $gruppe;
    }

    /**
     * Hent hvilken template som skal benyttes
     *
     * @return String $template_id
     */
    public function getTemplate() {
        $til = new Arrangement(get_option('pl_id'));
        $avsenderArrangementer = $til->getVideresending()->getAvsendere();
        
        UKMrapporter::addViewData('avsendere', $avsenderArrangementer);
        UKMrapporter::addViewData('til', $til);

        return 'Nominasjoner/rapport.html.twig';
    }

    /**
     * Hent spesifikk wordFormatter
     * 
     * @return FormatterNominasjoner
     */
    public function getWordFormatter()
    {
        $this->getConfig()->add(
            new ConfigValue(
                'arrangement_navn',
                $this->getArrangement()->getNavn()
            )
        );

        return new FormatterNominasjoner($this->getConfig());
    }
}