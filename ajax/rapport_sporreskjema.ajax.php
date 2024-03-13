<?php

// Spørreskjema

use UKMNorge\OAuth2\HandleAPICall;
use UKMNorge\Arrangement\Arrangement;
Use UKMNorge\Rapporter\Template\Node;
use UKMNorge\Arrangement\Skjema\SvarSett;

$handleCall = new HandleAPICall([], [], ['GET', 'POST'], false);

$arrangement = new Arrangement(get_option('pl_id'));

$root = new Node('Root', null);

$svarsett = [];
$alleArrangementer = [];

// Hent alle arrangementer som videresender 
foreach($arrangement->getVideresending()->getAvsendere() as $arrangAvsender) {
    $fraArrangement = $arrangAvsender->getArrangement();
    try{
        $skjemaFra = $fraArrangement->getSkjema();
        $svarsett[] = SvarSett::getPlaceholder('arrangement', $fraArrangement->getId(), $skjemaFra->getId());
        $alleArrangementer[] = $fraArrangement;
    }catch(Exception $e) {
        if($e->getCode() == 151002) {
            // Skjemma finnes ikke, fortsett videre fordi det er ikke nødvendigvis at alle arrangementer har sendt skjema
            continue;
        }
    }
}

$skjema = $arrangement->getSkjema();
foreach($alleArrangementer as $arrangement) {
    // Arrangement object array
    $arrangementObject = [
        'id' => $arrangement->getId(),
        'navn' => $arrangement->getNavn(),
        'type' => $arrangement->erMonstring() ? 'Arrangement' : 'Workshop',
        'sted' => $arrangement->getSted(),
    ];

    $arrangementNode = new Node('Arrangement', $arrangementObject);
    $root->addChild($arrangement->getId(), $arrangementNode);

    foreach($skjema->getSporsmalPerOverskrift() as $overskrift => $sporsmal) {
        foreach($sporsmal as $sporsmal) {

            $spormaalObject = [
                'id' => $sporsmal->getId(),
                'name' => $sporsmal->getTittel(),
                'type' => $sporsmal->getType()
            ];

            $sporsmalNode = new Node('Sporsmal', $spormaalObject);
            $arrangementNode->addChild($sporsmal->getId(), $sporsmalNode);

            foreach ($svarsett as $respondent) {
                $type =  $sporsmal->getType();
                $svar =  $respondent->get($sporsmal->getId())->getValue();
                
                $svarObject = [
                    'id' => $respondent->getId(),
                    'svar' => $svar,
                    'type' => $type
                ];
                
                $sporsmalNode->addChild($respondent->getId(), new Node('Svar', $svarObject));
            }

        }
    }
}

$arrRes = [
    'root' => $root,
    'status' => true,
];

$handleCall->sendToClient($arrRes);
