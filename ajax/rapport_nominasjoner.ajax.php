<?php

use UKMNorge\OAuth2\HandleAPICall;
use UKMNorge\Arrangement\Arrangement;
Use UKMNorge\Rapporter\Template\Node;
use UKMNorge\Rapporter\Template\Samling;


$handleCall = new HandleAPICall([], [], ['GET', 'POST'], false);

$arrangement = new Arrangement(get_option('pl_id'));


$til = $arrangement;
$avsenderArrangementer = $til->getVideresending()->getAvsendere();

$arrTyper = $til->getInnslagTyper();
$arrTyper =  array_filter( $arrTyper->getAll() , function($type) use ($til) { return $type->kanHaNominasjon() && $til->harNominasjonFor($type); });

$arrData = [];


$root = new Node('Root', null);
$innslagTyper = [];
$arrangementer = [];

$retObj = [];
foreach($arrTyper as $type) {
    foreach($avsenderArrangementer as $fra) {
        foreach($fra->getInnslag()->getAllByType($type) as $innslag) {
            
            // Adding type
            if(!key_exists($type->getNavn(), $innslagTyper)) {
                $innslagTyper[$type->getNavn()] = new Node('Type', $type);
                $root->addChild($type->getNavn(), $innslagTyper[$type->getNavn()]);
            }
            
            // Adding arrangement
            $fraArrangement = new Arrangement($fra->getPlId());
            $arrangementer[$fraArrangement->getId()] = new Node('Arrangement', $fraArrangement);
            $innslagTyper[$type->getNavn()]->addChild($fraArrangement->getId(), $arrangementer[$fraArrangement->getId()]);

            $nominert = $innslag->getNominasjoner()->harTil($til->getId());
            $nominasjon = $nominert ? $innslag->getNominasjoner()->getTil($til->getId()) : false;
            $person = $innslag->getPersoner()->getSingle();

            if($nominasjon->erNominert()) {
                $status = '';
                if($nominasjon->harVoksenSkjema() && $nominasjon->harDeltakerskjema()) {
                    $status = "Nominert";
                }
                else {
                    $status = "Nominert, men mangler skjema";
                }
                
                $videresendt = $innslag->erVideresendtTil($til);
                $nominasjonObj = [
                    'id' => $nominasjon->getId(),
                    'navn' => $innslag->getNavn(),
                    'mobil' => $innslag->getPerson()->getMobil(),
                    'epost' => $innslag->getPerson()->getEpost(),
                    'voksenskjema' => $nominasjon->harVoksenskjema(),
                    'deltakerskjema' => $nominasjon->harDeltakerskjema(),
                    'videresendt' =>  $videresendt,
                    'status' => $videresendt ? 'Videresendt' : '' . ($status . ' (' . (!$nominasjon->erAnswered() ? 'ikke besvart' : ($nominasjon->erGodkjent() ? 'godkjent' : 'ikke godkjent')) . ')')
                ];

                $nodeNominasjon = new Node('Nominasjon', $nominasjonObj);
                $arrangementer[$fraArrangement->getId()]->addChild($nominasjon->getId(), $nodeNominasjon);
            }
        }
    }
}


$arrRes = [
    'root' => $root,
    'status' => true,
];

$handleCall->sendToClient($arrRes);