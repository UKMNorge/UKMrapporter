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

foreach($arrangement->getProgram()->getAbsoluteAll() as $hendelse) {
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
            'rolle' => '',
            'alle_personer' => [],
        ];
        
        foreach($innslag->getPersoner()->getAll() as $person) {
            $person = $person;
            $person->alder = $person->getAlder();
            $innslagObj['alle_personer'][] = $person;
        }

        if($innslag->getType()->harBeskrivelse()) {
            if($innslag->getType()->erEnkeltperson() && $innslag->getType()->harFunksjoner()) {
               $innslagObj['rolle'] = $innslag->getPersoner()->getSingle()->getRolle(); 
            }
        }
        
        if($innslag->getType()->harTitler()) {
            foreach($innslag->getTitler()->getAll() as $tittel) {
                $innslagObj['navn'] = $tittel->getTittel() .' - '. $innslag->getNavn();
                $innslagObj['tid'] = $tittel->getVarighet()->getHumanShort();
                
                $innslags[$innslag->getId()] = new Node('Innslag', $innslagObj);
                $hendelser[$hendelse->getId()]->addChild($innslag->getId(), $innslags[$innslag->getId()]);
            }
        }
        else {
            $innslags[$innslag->getId()] = new Node('Innslag', $innslagObj);
            $hendelser[$hendelse->getId()]->addChild($innslag->getId(), $innslags[$innslag->getId()]);
        }

    }
}

$arrRes = [
    'root' => $root,
    'status' => true,
];

$handleCall->sendToClient($arrRes);