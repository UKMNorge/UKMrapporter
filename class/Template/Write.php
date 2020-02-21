<?php

namespace UKMNorge\Rapporter\Template;

use Exception;
use UKMNorge\Database\SQL\Insert;
use UKMNorge\Database\SQL\Update;

class Write {
    /**
     * Opprett et nytt template
     *
     * @param Int $user_id
     * @param Int $arrangementID
     * @param String $name
     * @return Template $template
     */
    public static function create( Int $userID, Int $arrangementID, String $rapportID, String $name ) {
        $query = new Insert('ukm_rapport_template');
        $query->add('user_id', $userID);
        $query->add('pl_id', $arrangementID);
        $query->add('name', $name);
        $query->add('report_id', $rapportID);
        $template_id = $query->run();

        if( !$template_id ) {
            echo $query->debug();
            throw new Exception(
                'Kunne ikke opprette mal',
                500001001
            );
        }

        return new Template(
            [
                'id' => $template_id,
                'report_id' => $rapportID,
                'user_id' => (Int) $userID,
                'pl_id' => (Int) $arrangementID,
                'name' => $name
            ]
        );
    }

    /**
     * Lagre gitt template
     *
     * @param Template $template
     * @throws Exception $save_error
     * @return Bool true
     */
    public static function save( Template $template ) {
        $db_template = Samling::getFromId( $template->getId() );

        $update = new Update(
            'ukm_rapport_template',
            [
                'id' => $template->getId()
            ]
        );

        $functions = [
            ['Id', 'id'],
            ['Navn', 'name'],
            ['RapportId', 'report_id'],
            ['BrukerId', 'user_id'],
            ['ArrangementId', 'pl_id'],
            ['Beskrivelse','description'],
            ['ConfigString','config']
        ];

        foreach( $functions as $functionData ) {
            $function = 'get'. $functionData[0];
            if( $template->$function() != $db_template->$function() ) {
                $update->add( $functionData[1], $template->$function() );
            }
        }

        if( !$update->hasChanges() ) {
            return true;
        }

        $res = $update->run();

        if( $res === false ) {
            echo $update->debug();
            throw new Exception(
                'Beklager, kunne ikke lagre mal',
                500001002
            );
        }
        return true;
    }
}