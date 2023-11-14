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
        $innslags[$innslag->getId()] = new Node('Innslag', $innslag);
        $hendelser[$hendelse->getId()]->addChild($innslag->getId(), $innslags[$innslag->getId()]);
    }
}

$arrRes = [
    'root' => $root,
    'status' => true,
];

$handleCall->sendToClient($arrRes);