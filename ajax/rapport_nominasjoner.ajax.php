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
$arrangementer = [];

$retObj = [];

foreach($arrTyper as $type) {
    foreach($avsenderArrangementer as $fra) {
        foreach($fra->getInnslag()->getAllByType($type) as $innslag) {
            
            // Adding arrangement
            $fraArrangement = new Arrangement($fra->getPlId());
            if(!key_exists($fra->getId(), $arrangementer)) {
                $arrangementer[$fra->getId()] = new Node('Arrangement', $fraArrangement);
                $root->addChild($fra->getId(), $arrangementer[$fra->getId()]);
            }

            $nominert = $innslag->getNominasjoner()->harTil($til->getId());
            $nominasjon = $nominert ? $innslag->getNominasjoner()->getTil($til->getId()) : false;
            $person = $innslag->getPersoner()->getSingle();

            if($nominasjon->erNominert()) {
                $nominasjonObj = [
                    'id' => $nominasjon->getId(),
                    'navn' => $innslag->getNavn(),
                    'voksenskjema' => $nominasjon->harVoksenskjema(),
                    'deltakerskjema' => $nominasjon->harDeltakerskjema(),
                    'videresendt' =>  $innslag->erVideresendtTil($til),
                ];

                $nodeNominasjon = new Node('Nominasjon', $nominasjonObj);
                $arrangementer[$fra->getId()]->addChild($nominasjon->getId(), $nodeNominasjon);
            }
        }
    }
}


$arrRes = [
    'root' => $root,
    'status' => true,
];

$handleCall->sendToClient($arrRes);