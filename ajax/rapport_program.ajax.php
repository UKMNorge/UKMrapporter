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

$alleHendelser = $arrangement->getProgram()->getAbsoluteAll();

usort($alleHendelser, function($a, $b) {
    return $a->getStart() < $b->getStart();
});

foreach($alleHendelser as $hendelse) {
    if(!key_exists($hendelse->getId(), $hendelser)) {
        $hendelser[$hendelse->getId()] = new Node('Hendelse', $hendelse);
        $root->prependChild($hendelse->getId(), $hendelser[$hendelse->getId()]);
    }

    foreach($hendelse->getInnslag()->getAll() as $innslag) {
        $innslagObj = [
            'id' => $innslag->getId(),
            'navn' => $innslag->getNavn(),
            'type' => $innslag->getType(),
            'sesong' => $innslag->getSesong(),
            'sjanger' => $innslag->getType()->getNavn() . ($innslag->getSjanger() ? ' - ' . $innslag->getSjanger() : ''),
            'fylke' => $innslag->getFylke() ? $innslag->getFylke()->getNavn() : 'Ukjent fylke',
            'kommuneId' => $innslag->getKommune()->getId(),
            'kommuneNavn' => $innslag->getKommune()->getNavn(),
            'beskrivelse' => $innslag->getBeskrivelse(),
            'oppmotetid' => $hendelse->getOppmoteTid($innslag),
            'rolle' => '',
            'alle_personer' => [],
            'alle_titler' => [],
        ];
        
        foreach($innslag->getPersoner()->getAll() as $person) {
            $person = $person;
            $person->alder = $person->getAlder();
            $innslagObj['alle_personer'][] = $person;
        }

        if($innslag->getType()->harBeskrivelse()) {
            if($innslag->getType()->erEnkeltperson() && $innslag->getType()->harFunksjoner()) {
                if($innslag->getPersoner()->getSingle()) {
                    $innslagObj['rolle'] = $innslag->getPersoner()->getSingle()->getRolle(); 
                }
            }
        }

        if($innslag->getType()->harTitler()) {
            foreach($innslag->getTitler()->getAll() as $tittel) {
                $innslagObj['tid'] = $innslagObj['tid'] + $tittel->getVarighetSomSekunder();
                $innslagObj['alle_titler'][] = $tittel;
            }
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