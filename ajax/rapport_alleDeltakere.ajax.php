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

    // Fylke kan være null
    if($fylke == null) {
        $fylke = new stdClass();
        $fylke->getId = function() { return 0; };
        $fylke->getNavn = function() { return 'Ukjent fylke'; };
    }

    if(!key_exists($fylke->getId(), $fylker)) {
        $fylker[$fylke->getId()] = new Node('Fylke', $fylke);
        $root->addChild($fylke->getId(), $fylker[$fylke->getId()]);
    }
    


    // Adding kommune
    if(!key_exists($kommune->getId(), $kommuner)) {
        $kommuneObj = ['id' => $kommune->getId(), 'navn' => $kommune->getNavn()];
        $kommuner[$kommune->getId()] = new Node('Kommune', $kommuneObj);
        $fylker[$fylke->getId()]->addChild($kommune->getId(), $kommuner[$kommune->getId()]);
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