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
$innslag = [];

$retObj = [];

foreach( $arrangement->getInnslag()->getAll() as $innslag ) {
    $fylke = $innslag->getFylke();
    $kommune = $innslag->getKommune();

    // Adding fylke
    $nodeFylke = new Node('Fylke', $fylke);
    $root->addChild($fylke->getId(), $nodeFylke);


    // Adding kommune
    // var_dump($kommune->getAll());

    $kommuneObj = ['id' => $kommune->getId(), 'navn' => $kommune->getNavn()];
    $nodeKommune = new Node('Kommune', $kommuneObj);
    $nodeFylke->addChild($kommune->getId(), $nodeKommune);
    
    // Adding innslag
    $nodeInnslag = new Node('Innslag', $innslag);
    $nodeKommune->addChild($innslag->getId(), $nodeInnslag);
    
    foreach( $innslag->getPersoner()->getAll() as $person ) {
        $nodeInnslag->addChild($person->getId(), $person);
    }
}


$arrRes = [
    'root' => $root,
    'status' => true,
];

$handleCall->sendToClient($arrRes);