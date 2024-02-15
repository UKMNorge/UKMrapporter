<?php

use UKMNorge\OAuth2\HandleAPICall;
use UKMNorge\Arrangement\Arrangement;
Use UKMNorge\Rapporter\Template\Node;
use UKMNorge\Rapporter\Template\Samling;


$handleCall = new HandleAPICall([], [], ['GET', 'POST'], false);

$arrangement = new Arrangement(get_option('pl_id'));


$root = new Node('Root', null);
$questions = [];

$retObj = [];



$deltakerskjema = $arrangement->getDeltakerSkjema();

foreach( $deltakerskjema->getSporsmalPerOverskrift() as $sporsmal ) {
    foreach( $sporsmal->getAll() as $question ) {
        $questionObj = [
            'id' => $question->getId(),
            'overskrift' => $sporsmal->getOverskrift(),
            'sporsmal' => $question->getTekst(),
            'type' => $question->getType(),
        ];

        $questions[$question->getId()] = new Node('Question', $questionObj);
        $root->addChild($question->getId(), $questions[$question->getId()]);

        foreach( $deltakerskjema->getRespondenter()->getAllPameldt($arrangement) as $respondent ) {            
            $respondentObj = [
                'id' => $respondent->getId(),
                'fornavn' => $respondent->getPerson()->getFornavn(),
                'etternavn' => $respondent->getPerson()->getEtternavn(),
                'epost' => $respondent->getPerson()->getEpost(),
                'fodselsdato' => $respondent->getPerson()->getFodselsdato(),
                'mobil' => $respondent->getPerson()->getMobil(),
                'svar' => $respondent->getSvar()->get( $question->getId() )->getValue()
            ];

            $respondenter[$respondent->getId()] = new Node('Person', $respondentObj);
            $questions[$question->getId()]->addChild($respondent->getId(), $respondenter[$respondent->getId()]);
        }
    }
}

    // Adding fylke
    // if(!key_exists($fylke->getId(), $questions)) {
    //     $fylker[$fylke->getId()] = new Node('Question', $fylke);
    //     $root->addChild($fylke->getId(), $fylker[$fylke->getId()]);
    // }
    
    // foreach( $innslag->getPersoner()->getAll() as $person ) {
    //     $nodeInnslag->addChild($person->getId(), $person);
    // }



$arrRes = [
    'root' => $root,
    'status' => true,
];

$handleCall->sendToClient($arrRes);