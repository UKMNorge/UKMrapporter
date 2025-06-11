<?php

use UKMNorge\OAuth2\HandleAPICall;
use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Arrangement\Videresending\Ledere\Ledere;
use UKMNorge\Arrangement\UKMFestival;
use UKMNorge\Geografi\Fylker;
use UKMNorge\Rapporter\Template\Node;

$handleCall = new HandleAPICall([], [], ['GET', 'POST'], false);

$til = new Arrangement(get_option('pl_id'));

if ($til->getEierType() == 'land') {
    $til = new UKMFestival($til->getId());
    UKMrapporter::addViewData('overnattingGrupper', $til->getOvernattingGrupper());
}

$selectedFylker = Fylker::getAll();
$root = new Node('Root', null);
$fylkerArr = [];

$alleGyldigeNetter = [];
foreach ($til->getNetter() as $natt) {
    $alleGyldigeNetter[$natt->format('d_m')] = $natt;
}

foreach ($til->getVideresending()->getAvsendere() as $avsender) {
    $fra = $avsender->getArrangement();
    foreach ($selectedFylker as $fylke) {
        if ($fylke->getId() == $fra->getFylke()->getId()) {
            $ledere = new Ledere($fra->getId(), $til->getId());
            foreach ($ledere->getAll() as $leder) {
                foreach ($leder->getNetter()->getAll() as $natt) {
                    if ($alleGyldigeNetter[$natt->getId()] && $natt->getSted() == 'hotell') {

                        if (!isset($fylkerArr[$fylke->getId()])) {
                            $fylkeObj = [
                                'id' => $fylke->getId(),
                                'navn' => $fylke->getNavn(),
                            ];
                            $fylkerArr[$fylke->getId()] = new Node('Fylke', $fylkeObj);
                            $root->addChild($fylke->getId(), $fylkerArr[$fylke->getId()]);
                        }

                        $fylkeNode = $fylkerArr[$fylke->getId()];

                        if (!isset($netter[$fylke->getId()])) {
                            $netter[$fylke->getId()] = [];
                        }

                        if (!isset($netter[$fylke->getId()][$natt->getId()])) {
                            $nattObj = [
                                'id' => $natt->getId(),
                                'dato' => $natt->getDato() . '_' . $til->getSesong(),
                                'sted' => $natt->getSted(),
                            ];
                            $netter[$fylke->getId()][$natt->getId()] = new Node('Natt', $nattObj);
                            $fylkeNode->addChild($natt->getId(), $netter[$fylke->getId()][$natt->getId()]);
                        }

                        $nattNode = $netter[$fylke->getId()][$natt->getId()];

                        $lederObj = [
                            'id' => $leder->id,
                            'navn' => $leder->navn,
                            'type' => $leder->type,
                            'mobil' => $leder->mobil,
                            'epost' => $leder->epost,
                            'fylkeId' => $fylke->getId(),
                            'fylkeNavn' => $fylke->getNavn(),
                            'godkjent' => $leder->getGodkjent() == 1,
                        ];

                        $lederNode = new Node('Leder', $lederObj);
                        $nattNode->addChild($leder->getId(), $lederNode);
                    }
                }
            }
        }
    }
}

$arrRes = [
    'root' => $root,
    'status' => true,
];

$handleCall->sendToClient($arrRes);