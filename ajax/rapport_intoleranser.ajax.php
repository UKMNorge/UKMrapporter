<?php

use UKMNorge\OAuth2\HandleAPICall;
use UKMNorge\Arrangement\Arrangement;
Use UKMNorge\Rapporter\Template\Node;
use UKMNorge\Geografi\Fylker;
use UKMNorge\Arrangement\Videresending\Ledere\Ledere;
use UKMNorge\Sensitivt\Requester;
use UKMNorge\Sensitivt\Sensitivt;

$handleCall = new HandleAPICall([], [], ['GET', 'POST'], false);

$root = new Node('Root', null);
$fylker = [];
$arrangementer = [];

Sensitivt::setRequester(
    new Requester(
        'wordpress', 
        wp_get_current_user()->ID,
        get_option('pl_id')
    )
);

$til = new Arrangement(get_option('pl_id'));

// Legger til Person(er) som er del av arrangementet
foreach( $til->getInnslag()->getAll() as $innslag ) {
    $innslagArrangement = $innslag->getHome();
    
    $fylke = $innslag->getFylke();
    $hasFylke = $fylke != null;

    // Fylke kan være null
    if($fylke == null) {
        $fylke = new stdClass();
        $fylke->getId = function() { return 'undefinedfylke'; };
        $fylke->getNavn = function() { return 'Ukjent fylke'; };
    }

    $fylkeId = $hasFylke ? $fylke->getId() : ($fylke->getId)();
    $fylkeNavn = $hasFylke ? $fylke->getNavn() : ($fylke->getNavn)();

    // Adding fylke
    if(!key_exists($fylkeId, $fylker)) {
        $fylker[$fylkeId] = new Node('Fylke', $fylke);
        $root->addChild($fylkeId, $fylker[$fylkeId]);
    }

    // Adding arrangement
    if(!key_exists($innslagArrangement->getId(), $arrangementer)) {
        $arrangementer[$innslagArrangement->getId()] = new Node('Arrangement', $innslagArrangement);
        $fylker[$fylkeId]->addChild($innslagArrangement->getId(), $arrangementer[$innslagArrangement->getId()]);
    }

    foreach( $innslag->getPersoner()->getAll() as $person ) {
        if( $person->getSensitivt()->getIntoleranse()->har() ) {
            $personObj = [
                'id' => $person->getId(),
                'alder' => $person->getFodselsdato(),
                'mobil' => $person->getMobil(),
                'navn' =>  $person->getNavn(),
                'epost' => $person->getEpost(),
                'listeIntoleranser' => $person->getSensitivt()->getIntoleranse()->getListeHuman(true),
                'tekstIntoleranser' => $person->getSensitivt()->getIntoleranse()->getTekst(),
            ];
    
            // Adding Person
            $nodeLeder = new Node('Person', $personObj);
            $arrangementer[$innslagArrangement->getId()]->addChild($person->getId(), $nodeLeder);
        }
    }
}

// Adding leder som ble send videresend til dette arrangementet
foreach($til->getVideresending()->getAvsendere() as $avsender) {

    $fra = $avsender->getArrangement();
    $fylke = $fra->getFylke();
    $hasFylke = $fylke != null;

    // Fylke kan være null
    if($fylke == null) {
        $fylke = new stdClass();
        $fylke->getId = function() { return 'undefinedfylke'; };
        $fylke->getNavn = function() { return 'Ukjent fylke'; };
    }

    $fylkeId = $hasFylke ? $fylke->getId() : ($fylke->getId)();
    $fylkeNavn = $hasFylke ? $fylke->getNavn() : ($fylke->getNavn)();
    
    // Adding fylke
    if(!key_exists($fylkeId, $fylker)) {
        $fylker[$fylkeId] = new Node('Fylke', $fylke);
        $root->addChild($fylkeId, $fylker[$fylkeId]);
    }

    // Adding arrangement
    if(!key_exists($fra->getId(), $arrangementer)) {
        $arrangementer[$fra->getId()] = new Node('Arrangement', $fra);
        $fylker[$fylkeId]->addChild($fra->getId(), $arrangementer[$fra->getId()]);
    }


    $ledere = new Ledere($fra->getId(), $til->getId());
    foreach($ledere->getAll() as $leder) {
        if( !$leder->getSensitivt()->getIntoleranse()->har() ) {
            continue;
        }
        
        $leder->setNavn($leder->getNavn() . ' ('. $leder->getTypeNavn() .')');

        $personObj = [
            'id' => $leder->getId(),
            'navn' => $leder->getNavn(),
            'alder' => 0, // Leder har ikke alder
            'mobil' => $leder->getMobil(),
            'epost' => $leder->getEpost(),
            'listeIntoleranser' => $leder->getSensitivt()->getIntoleranse()->getListeHuman(true),
            'tekstIntoleranser' => $leder->getSensitivt()->getIntoleranse()->getTekst(),
        ];

        // Adding Person (selv om det kommer fra Leder)
        $nodeLeder = new Node('Person', $personObj);
        $arrangementer[$fra->getId()]->addChild($leder->getId(), $nodeLeder);
    }
}


$arrRes = [
    'root' => $root,
    'status' => true,
];

$handleCall->sendToClient($arrRes);