<?php

use UKMNorge\OAuth2\HandleAPICall;
use UKMNorge\Arrangement\Arrangement;
Use UKMNorge\Rapporter\Template\Node;
use UKMNorge\Rapporter\Template\Samling;
use UKMNorge\Arrangement\Videresending\Ledere\Ledere;


$handleCall = new HandleAPICall([], [], ['GET', 'POST'], false);

$root = new Node('Root', null);
$fylker = [];
$arrangementer = [];

// Ledere
$fylkeLedere = [];
$til = new Arrangement(get_option('pl_id'));

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

    $fylkeLedere[$fylke->getId()]['fylke'] = $fylke;
    foreach($ledere->getAll() as $leder) {
        // turist, ledsager og sykerom blir ikke med i rapporten
        if(!in_array($leder->getType(), ['sykerom'])) {
            $leder->getArrangementFra();
            // $fylkeLedere[$fylke->getId()]['ledere'][] = $leder;
        

            $lederObj = [
                'id' => $leder->getId(),
                'navn' => $leder->getNavn(),
                'type' => $leder->getTypeNavn(),
                'mobil' => $leder->getMobil(),
                'epost' => $leder->getEpost(),
                'godkjent' => ($til->getType() != 'land' ? true : ($leder->getGodkjent() == 1)),
            ];

            // Adding leder
            $nodeLeder = new Node('Leder', $lederObj);
            $arrangementer[$fra->getId()]->addChild($leder->getId(), $nodeLeder);
        
        }
    }
}

$arrRes = [
    'root' => $root,
    'status' => true,
];

$handleCall->sendToClient($arrRes);