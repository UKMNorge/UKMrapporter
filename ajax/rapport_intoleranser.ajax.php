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
    $fylke = $innslag->getFylke();

    // Adding fylke
    if(!key_exists($fylke->getId(), $fylker)) {
        $fylker[$fylke->getId()] = new Node('Fylke', $fylke);
        $root->addChild($fylke->getId(), $fylker[$fylke->getId()]);
    }

    // Adding arrangement
    if(!key_exists($til->getId(), $arrangementer)) {
        $arrangementer[$til->getId()] = new Node('Arrangement', $til);
        $fylker[$fylke->getId()]->addChild($til->getId(), $arrangementer[$til->getId()]);
    }

    foreach( $innslag->getPersoner()->getAll() as $person ) {

        $fylke_gruppe_id = $innslag->getFylke()->getNavn() . '-' . $innslag->getFylke()->getId();
        if( $person->getSensitivt()->getIntoleranse()->har() ) {
            
            $personObj = [
                'id' => $person->getId(),
                'alder' => $person->getFodselsdato(),
                'mobil' => $person->getMobil(),
                'navn' => "PERSON " . $person->getNavn(),
                'epost' => $person->getEpost(),
                'listeIntoleranser' => $person->getSensitivt()->getIntoleranse()->getListeHuman(true),
                'tekstIntoleranser' => $person->getSensitivt()->getIntoleranse()->getTekst(),
            ];
    
            // Adding Person
            $nodeLeder = new Node('Person', $personObj);
            $arrangementer[$til->getId()]->addChild($person->getId(), $nodeLeder);

        }
    }
}

// Adding leder som ble send videresend til dette arrangementet
foreach($til->getVideresending()->getAvsendere() as $avsender) {

    $fra = $avsender->getArrangement();
    $fylke = $fra->getFylke();
    
    // Adding fylke
    if(!key_exists($fylke->getId(), $fylker)) {
        $fylker[$fylke->getId()] = new Node('Fylke', $fylke);
        $root->addChild($fylke->getId(), $fylker[$fylke->getId()]);
    }

    // Adding arrangement
    if(!key_exists($fra->getId(), $arrangementer)) {
        $arrangementer[$fra->getId()] = new Node('Arrangement', $fra);
        $fylker[$fylke->getId()]->addChild($fra->getId(), $arrangementer[$fra->getId()]);
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









