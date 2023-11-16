<?php

use UKMNorge\OAuth2\HandleAPICall;
use UKMNorge\Arrangement\Arrangement;
Use UKMNorge\Rapporter\Template\Node;
use UKMNorge\Rapporter\Template\Samling;

$handleCall = new HandleAPICall([], [], ['GET', 'POST'], false);

$arrangement = new Arrangement(get_option('pl_id'));

$root = new Node('Root', null);
$hendelser = [];
$innslags = [];

$retObj = [];


foreach( $arrangement->getProgram()->getAbsoluteAll() as $hendelse ) {
    // Adding hendelse
    if(!key_exists($hendelse->getId(), $hendelser)) {
        $hendelser[$hendelse->getId()] = new Node('Hendelse', $hendelse);
        $root->addChild($hendelse->getId(), $hendelser[$hendelse->getId()]);
    }

    foreach($hendelse->getInnslag()->getAll() as $innslag) {
        $innslagObj = $innslag;
        
        foreach($innslag->getSamtykke()->getAll() as $person) {
            $personObj = [
                'id' => $person->getId(),
                'navn' => $person->getNavn(),
                'status' => $person->getStatus()->getId() != "ikke_godkjent" ? "OK" : "Ikke godkjent samtykke",
                'foresatt' => null,
                'foresatt_mobil' => null,
                'kategori' => $person->getKategori()->getId(),
            ];
            // Personen er under 15 og derfor er det foresatt som skal utføre godkjenning
            if($person->getKategori()->getId() == 'u15') {
                $personObj['foresatt'] = $person->getForesatt()->getNavn();
                $personObj['foresatt_mobil'] = $person->getForesatt()->getMobil();
                $personObj['foresatt_status'] = $person->getForesatt()->getStatus()->getId() != "ikke_godkjent" ? "OK" : "Ikke godkjent samtykke";
            }
            $innslagObj->alle_personer[] = $personObj;
        }
        
        try {
            foreach($innslag->getTitler()->getAll() as $tittel) {
                $innslagObj->alle_titler[] = $tittel;
            }
        } catch(Exception $e) {
            // Tittel kan ikke opprettes. Mest sannsynlig finnes det ikke klasse per noen innslag typer som kan ikke ha titler
        }

        $innslags[$innslag->getId()] = new Node('Innslag', $innslagObj);
        $hendelser[$hendelse->getId()]->addChild($innslag->getId(), $innslags[$innslag->getId()]);
    }
}

$arrRes = [
    'root' => $root,
    'status' => true,
];

$handleCall->sendToClient($arrRes);