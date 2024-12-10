<?php

use UKMNorge\OAuth2\HandleAPICall;
use UKMNorge\Arrangement\Arrangement;
Use UKMNorge\Rapporter\Template\Node;
use UKMNorge\Rapporter\Template\Samling;

$handleCall = new HandleAPICall([], [], ['GET', 'POST'], false);

$arrangement = new Arrangement(get_option('pl_id'));

$root = new Node('Root', null);
// $hendelser = [];
$innslags = [];

$retObj = [];


foreach( $arrangement->getInnslag()->getAll() as $innslag ) {
    $innslagObj = [
        'id' => $innslag->getId(),
        'navn' => $innslag->getNavn(),
        'type' => $innslag->getType()->getKey(),
        'sesong' => $innslag->getSesong(),
        'arrangement' => $innslag->getHome()->getNavn(),
        'fylke' => $innslag->getFylke() ? $innslag->getFylke()->getNavn() : 'Ukjent fylke',
        'alle_personer' => [],
        'alle_titler' => [],
    ];
    
    foreach($innslag->getSamtykke()->getAll() as $person) {
        $innslagPerson = $person->getPerson();
        $personObj = [
            'id' => $person->getId(),
            'fornavn' => $innslagPerson->getFornavn(),
            'etternavn' => $innslagPerson->getEtternavn(),
            'mobil' => $innslagPerson->getMobil(),
            'epost' => $innslagPerson->getEpost(),
            'status' => $person->getStatus()->getId() == 'godkjent' ? "Godkjent samtykke" : "Ikke godkjent samtykke",
            'foresatt' => null,
            'foresatt_mobil' => null,
            'kategori' => $person->getKategori()->getId(),
            'godkjent' => $person->getStatus()->getId() == 'godkjent' ? true : false,
            'stat' => $person->getStatus()->getId(),
        ];
        // Personen er under 15 og derfor er det foresatt som skal utføre godkjenning
        if($person->getKategori()->getId() == 'u15') {
            $personObj['foresatt'] = $person->getForesatt()->getNavn();
            $personObj['foresatt_mobil'] = $person->getForesatt()->getMobil();
            $personObj['foresatt_status'] = $person->getForesatt()->getStatus()->getId() == 'ikke_sendt' ? 'Samtykke ikke sendt' : ($person->getForesatt()->getStatus()->getId() != "ikke_godkjent" ? "Godkjent samtykke" : "Ikke godkjent samtykke");
        }
        $innslagObj['alle_personer'][] = $personObj;
    }
    
    try {
        foreach($innslag->getTitler()->getAll() as $tittel) {
            $innslagObj['alle_titler'][] = $tittel;
        }
    } catch(Exception $e) {
        // Tittel kan ikke opprettes. Mest sannsynlig finnes det ikke klasse per noen innslag typer som kan ikke ha titler
    }

    // Adding hendelse
    if(!key_exists($innslag->getId(), $innslags)) {
        $innslags[$innslag->getId()] = new Node('Innslag', $innslagObj);
        $root->addChild($innslag->getId(), $innslags[$innslag->getId()]);
    }
    // $hendelser[$hendelse->getId()]->addChild($innslag->getId(), $innslags[$innslag->getId()]);
}

$arrRes = [
    'root' => $root,
    'status' => true,
];

$handleCall->sendToClient($arrRes);