<?php

namespace UKMNorge\Rapporter;

use UKMnettverket;
use UKMNorge\Innslag\Samling;
use UKMNorge\Rapporter\CustomItems\Administrator;
use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;

class Lokalkontakter extends Rapport
{
    public $kategori_id = 'user_kontakt';
    public $ikon = 'dashicons-universal-access';
    public $navn = 'Lokalkontakter';
    public $beskrivelse = 'Kontaktinfo til alle dine lokalkontakter';

    /**
     * Hent alle fylke-områder current user kan administrere
     *
     * @return Array<Omrade>
     */
    public function getOmrader() {
        return UKMnettverket::getCurrentAdmin()->getOmrader('fylke');
    }

    /**
     * Hent hvilken template som skal benyttes
     *
     * @return String $template_id
     */
    public function getTemplate()
    {
        return 'Lokalkontakter/rapport.html.twig';
    }

    public function getRenderData() {
        $data = new Gruppe('lokalkontakter', 'Lokalkontakter');

        foreach( $this->getOmrader() as $omrade ) {
            $fylke = $omrade->getFylke();

            foreach( $fylke->getKommuner()->getAll() as $kommune ) {
                // Hvis brukeren kun ønsker utvalgte kommuner fra fylket
                // og denne kommunen ikke er en av de valgte: skip.
                if( !$this->getConfig()->vis('valgte_kommuner_i_'. $fylke->getId()) && !$this->getConfig()->vis('kommune_'. $kommune->getId() )) {
                    continue;
                }

                if($data->harGruppe('kommune_'. $kommune->getId())) {
                    $kommuneGruppe = $data->getGruppe('kommune_'. $kommune->getId());
                } else {
                    $kommuneGruppe = new Gruppe('kommune_'. $kommune->getId(), $kommune->getNavn());
                    $data->addGruppe($kommuneGruppe);
                }
                
                foreach( $kommune->getOmrade()->getAdministratorer()->getAll() as $admin ) {
                    $administrator = new Administrator( $admin ) ;
                    $administrator->setKommune($kommune);
                    $kommuneGruppe->addCustomItem( $administrator );
                }
            }
        }
        return $data;
    }

    /**
     * Hent excel-fil (og få med arket titler)
     *
     * @inheritdoc
     */
    public function getExcelFile()
    {
        $this->getConfig()->get('vis_titler')->setValue(true);
        return parent::getExcelFile();
    }
}