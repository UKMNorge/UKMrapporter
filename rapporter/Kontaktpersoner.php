<?php

namespace UKMNorge\Rapporter;

use UKMNorge\Database\SQL\Query;
use UKMNorge\Innslag\Innslag;
use UKMNorge\Innslag\Samling;
use UKMNorge\Innslag\Typer\Typer;
use UKMNorge\Rapporter\Framework\Gruppe;
use UKMNorge\Rapporter\Framework\Rapport;

class Kontaktpersoner extends Rapport
{
    public $kategori_id = 'network_delta';
    public $ikon = 'dashicons-awards';
    public $navn = 'Antall innslag per kontaktperson';
    public $beskrivelse = 'Hvor mange kontaktpersoner melder på flere innslag';
    public $har_excel = false;
    public $har_sms = false;
    public $har_epost = false;

    /**
     * Hent alle innslag som skal sorteres
     *
     * @return Array<Innslag>
     */
    public function getInnslag()
    {
        return $this->getArrangement()->getInnslag()->getAll();
    }

    /**
     * Hent alle innslagTyper vi skal vise
     *
     * @return Typer
     */
    public function getInnslagTyper()
    {
        return $this->getArrangement()->getInnslagTyper(true);
    }

    public function getRenderData()
    {

        $pameldt = true;

        $this->main_group = new Gruppe('container', 'Antall innslag');

        $keys = [
            '1' => '1 innslag',
            '2' => '2 innslag',
            '3' => '3 innslag',
            '4' => '4 innslag',
            '5' => '5 innslag',
            '6_10' => '6-10 innslag',
            '11_15' => '11-15 innslag',
            '16_20' => '16-20 innslag',
            '21_30' => '21-30 innslag',
            '30_over' => '31+ innslag',
            '1_2' => '1-2 innslag',
            '3_5' => '3-5 innslag',
            '10_over' => '10+ innslag',
        ];
        foreach ($keys as $id => $name) {
            $this->main_group->addGruppe(new Gruppe($id, $name));
        }

        foreach ([false,true] as $pameldt) {
            $query = new Query(
                "SELECT `ant_innslag`, COUNT(`b_contact`) AS `ant_tilfeller`
                FROM (
                    SELECT `b_contact`, COUNT(`b_id`) AS `ant_innslag`
                    FROM `smartukm_band`
                    WHERE `b_season` = #sesong
                    AND `b_status` " . ($pameldt ? '=' : '!=') . " 8
                    GROUP BY `b_contact`
                    ORDER BY `ant_innslag`
                ) AS `temp_contact_count`
                GROUP BY `ant_innslag`
            ",
                [
                    'sesong' => (int) $this->getConfig()->get('vis_sesong')->getValue()
                ]
            );
            #echo $query->debug();
            $res = $query->run();

            while ($row = Query::fetch($res)) {
                $this->addToGroups(
                    $this->getKeysFromRow(intval($row['ant_innslag'])),
                    intval($row['ant_tilfeller']),
                    $pameldt
                );
            }
        }
        #var_dump($this->main_group);
        foreach( $this->main_group->getGrupper() as $gruppe ) {
            $gruppe->setAttr('totalt',
                intval( $gruppe->getAttr('pameldt') ) + intval( $gruppe->getAttr('ikke_pameldt') )
            );
        }
        return $this->main_group;
    }

    private function addToGroups(Array $keys, Int $antall_tilfeller, Bool $pameldt) {
        foreach( $keys as $key ) {
            $gruppe = $this->main_group->getGruppe($key);
            $attr_key = (!$pameldt ? 'ikke_' : '') . 'pameldt';
            
            if( is_null($gruppe->getAttr( $attr_key))) {
                $gruppe->setAttr( $attr_key, $antall_tilfeller );
            } else {
                $gruppe->setAttr(
                    $attr_key,
                    ((int) $gruppe->getAttr($attr_key) + $antall_tilfeller)
                );
            }
        }
    }

    private function getKeysFromRow(Int $antall_innslag)
    {
        switch ($antall_innslag) {
            case 1:
                return ['1', '1_2'];
            case 2:
                return ['2', '1_2'];
            case 3:
                return ['3', '3_5'];
            case 4:
                return ['4', '3_5'];
            case 5:
                return ['5', '3_5'];
            case 6:
                return ['6', '6_10'];
            case 7:
            case 8:
            case 9:
                return ['6_10'];
            case 10:
                return ['6_10', '10_over'];
            case 11:
            case 12:
            case 13:
            case 14:
            case 15:
                return ['11_15', '10_over'];
            case 16:
            case 17:
            case 18:
            case 19:
            case 20:
                return ['16_20', '10_over'];
            case 21:
            case 22:
            case 23:
            case 24:
            case 25:
            case 26:
            case 27:
            case 28:
            case 29:
            case 30:
                return ['21_30', '10_over'];
            default:
                return ['10_over', '30_over'];
        }
    }

    /**
     * Hent hvilken template som skal benyttes
     *
     * @return String $template_id
     */
    public function getTemplate()
    {
        return 'Kontaktpersoner/rapport.html.twig';
    }
}
