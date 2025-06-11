<?php

use UKMNorge\OAuth2\HandleAPICall;
use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Rapporter\Template\Node;

$handleCall = new HandleAPICall([], [], ['GET', 'POST'], false);

$arrangement = new Arrangement(get_option('pl_id'));
$root = new Node('Root', null);

$statusNodes = [
    'godkjent' => new Node('DefaultNode', ['id' => 'godkjent', 'navn' => 'Godkjent']),
    'ikke_godkjent' => new Node('DefaultNode', ['id' => 'ikke_godkjent', 'navn' => 'Ikke godkjent'])
];

foreach ($arrangement->getInnslag()->getAll() as $innslag) {
    $statusFlags = [
        'godkjent' => false,
        'ikke_godkjent' => false
    ];

    $innslagObj = [
        'id' => $innslag->getId(),
        'navn' => $innslag->getNavn(),
        'type' => $innslag->getType()->getKey(),
        'sesong' => $innslag->getSesong(),
        'arrangement' => $innslag->getHome()->getNavn(),
        'fylke' => $innslag->getFylke() ? $innslag->getFylke()->getNavn() : 'Ukjent fylke',
        'alle_personer' => [],
        'alle_titler' => []
    ];

    try {
        foreach ($innslag->getTitler()->getAll() as $tittel) {
            $innslagObj['alle_titler'][] = $tittel;
        }
    } catch (Exception $e) {
        // No titles available, continue without adding to the object   
    }

    foreach ($innslag->getSamtykke()->getAll() as $person) {
        if($person->getStatus()->getId() != 'godkjent') {
            $personStatusId = 'ikke_godkjent';
        }
        $statusId = $person->getStatus()->getId();
        $statusKey = $statusId == 'godkjent' ? 'godkjent' : 'ikke_godkjent';
        $statusFlags[$statusKey] = true;

        $innslagPerson = $person->getPerson();
        $personObj = [
            'id' => $person->getId(),
            'fornavn' => $innslagPerson->getFornavn(),
            'etternavn' => $innslagPerson->getEtternavn(),
            'mobil' => $innslagPerson->getMobil(),
            'epost' => $innslagPerson->getEpost(),
            'status' => $statusId == 'godkjent' ? "Godkjent samtykke" : "Ikke godkjent samtykke",
            'foresatt' => null,
            'foresatt_mobil' => null,
            'kategori' => $person->getKategori()->getId(),
            'godkjent' => $statusId == 'godkjent',
            'stat' => $statusId,
        ];

        if ($person->getKategori()->getId() == 'u15') {
            $personObj['foresatt'] = $person->getForesatt()->getNavn();
            $personObj['foresatt_mobil'] = $person->getForesatt()->getMobil();
            $personObj['foresatt_status'] = $person->getForesatt()->getStatus()->getId() == 'ikke_sendt'
                ? 'Samtykke ikke sendt'
                : ($person->getForesatt()->getStatus()->getId() != "ikke_godkjent"
                    ? "Godkjent samtykke"
                    : "Ikke godkjent samtykke");
        }

        $innslagObj['alle_personer'][] = $personObj;
    }

    foreach ($statusFlags as $statusKey => $used) {
        if ($used) {
            $innslagNode = new Node('Innslag', $innslagObj);
            $statusNodes[$statusKey]->addChild($innslag->getId(), $innslagNode);
        }
    }
}

$root->addChild('godkjent', $statusNodes['godkjent']);
$root->addChild('ikke_godkjent', $statusNodes['ikke_godkjent']);

$arrRes = [
    'root' => $root,
    'status' => true
];

$handleCall->sendToClient($arrRes);