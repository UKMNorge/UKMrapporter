<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Arrangement\Load;
use UKMNorge\Innslag\Context\Context;
use UKMNorge\Innslag\Samling;
use UKMNorge\Innslag\Typer\Typer;
use UKMNorge\Rapporter\Framework\Rapport;

class NokkeltallNorge extends Nokkeltall
{
    public $kategori_id = 'network_delta';
    public $ikon = 'dashicons-chart-pie';
    public $navn = 'Nøkkeltall';
    public $beskrivelse = 'Hvor mange påmeldte er det i hver kategori?';
    public $har_excel = false;
    public $har_sms = false;
    public $har_epost = false;
    private $typer = null;
    private $innslag = null;

    /**
     * Hent alle innslag (fra sesongen 🤯) som skal sorteres
     *
     * @return Array<Innslag>
     */
    public function getInnslag()
    {
        if( is_null($this->innslag) ) {

            $arrangementer = Load::bySesong( (int) $this->getConfig()->get('vis_sesong')->getValue() );
            $this->innslag = [];
            foreach( $arrangementer->getAll() as $arrangement ) {
                foreach( $arrangement->getInnslag()->getAll() as $innslag ) {
                    $this->innslag[] = $innslag;
                }
            }
        }
        
        return $this->innslag;
    }

    /**
     * Hent alle innslagTyper vi skal vise
     *
     * @return Typer
     */
    public function getInnslagTyper()
    {
        // Hvis type er workshop, da skal hentes alle type enkelperson
        $arrangementType = $this->getConfig()->get('arrangement_type')->getValue();
        if($arrangementType == 'workshop') {
            return $this->getInnslagTyperWorkshop();
        }

        if (is_null($this->typer)) {
            $this->typer = new Typer();
            foreach (Typer::getAlleInkludertSkjulteTyper() as $type) {
                $this->typer->add($type);
            }
        }
        return $this->typer;
    }

    /**
     * Hent alle typer som gjelder for workshop
     *
     * @return Typer
     */
    public function getInnslagTyperWorkshop()
    {
        $this->typer = new Typer();
        $this->typer->add(Typer::getByKey('enkeltperson'));
        
        return $this->typer;
    }
}
