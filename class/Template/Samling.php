<?php

namespace UKMNorge\Rapporter\Template;

use Exception;
use UKMNorge\Collection;
use UKMNorge\Database\SQL\Query;

class Samling extends Collection
{

    /**
     * Hent alle templates for en rapport (og arrangement)
     *
     * @param String $rapportID
     * @param Int $arrangementID
     * @return Samling<Template>
     */
    public static function getFromRapport(String $rapportID, Int $arrangementID)
    {
        $samling = new Samling();

        $query = new Query(
            "SELECT * 
            FROM `ukm_rapport_template`
            WHERE `report_id` = '#rapport'
            AND `pl_id` = '#arrangement'
            ORDER BY `name` ASC",
            [
                'rapport' => $rapportID,
                'arrangement' => $arrangementID
            ]
        );
        $res = $query->getResults();

        while( $row = Query::fetch( $res ) ) {
            $samling->add( new Template( $row ) );
        }

        return $samling;
    }

    /**
     * Hent template fra gitt ID
     *
     * @param Int $id
     * @return Template $template
     */
    public static function getFromId(Int $id)
    {
        $query = new Query(
            "SELECT * 
            FROM `ukm_rapport_template`
            WHERE `id` = '#id'",
            [
                'id' => $id
            ]
        );
        $data = $query->getArray();

        if (null == $data) {
            throw new Exception(
                'Beklager, fant ikke mal ' . $id,
                100001002
            );
        }
        return new Template(
            $data
        );
    }
}
