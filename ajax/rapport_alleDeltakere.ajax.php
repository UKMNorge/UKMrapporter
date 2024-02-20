<?php

use UKMNorge\OAuth2\HandleAPICall;
use UKMNorge\Arrangement\Arrangement;
Use UKMNorge\Rapporter\Template\Node;
use UKMNorge\Rapporter\Template\Samling;


$handleCall = new HandleAPICall([], [], ['GET', 'POST'], false);

$arrangement = new Arrangement(get_option('pl_id'));


$root = new Node('Root', null);
$fylker = [];
$kommuner = [];
$innslags = [];

$retObj = [];

foreach( $arrangement->getInnslag()->getAll() as $innslag ) {
    $fylke = $innslag->getFylke();
    $kommune = $innslag->getKommune();
    $hasFylke = $fylke != null;

    // Fylke kan 
    if($fylke == null) {
        $fylke = new stdClass();
        $fylke->getId = function() { return 'undefinedfylke'; };
        $fylke->getNavn = function() { return 'Ukjent fylke'; };
    }

    $fylkeId = $hasFylke ? $fylke->getId() : ($fylke->getId)();
    $fylkeNavn = $hasFylke ? $fylke->getNavn() : ($fylke->getNavn)();

    if(!key_exists($fylkeId, $fylker)) {
        $fylkeObj = [
            'id' => $fylkeId,
            'navn' => $fylkeNavn,
        ];

        $fylker[$fylkeId] = new Node('Fylke', $fylkeObj);
        $root->addChild($fylkeId, $fylker[$fylkeId]);
    }
    


    // Adding kommune
    if(!key_exists($kommune->getId(), $kommuner)) {
        $kommuneObj = ['id' => $kommune->getId(), 'navn' => $kommune->getNavn()];
        $kommuner[$kommune->getId()] = new Node('Kommune', $kommuneObj);
        $fylker[$fylkeId]->addChild($kommune->getId(), $kommuner[$kommune->getId()]);
    }
    
    // Adding innslag
    $nodeInnslag = new Node('Innslag', $innslag);
    $kommuner[$kommune->getId()]->addChild($innslag->getId(), $nodeInnslag);
    
    foreach( $innslag->getPersoner()->getAll() as $person ) {
        $nodeInnslag->addChild($person->getId(), $person);
    }
}


$arrRes = [
    'root' => $root,
    'status' => true,
];

$handleCall->sendToClient($arrRes);