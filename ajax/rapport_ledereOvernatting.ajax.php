<?php

use UKMNorge\OAuth2\HandleAPICall;
use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Rapporter\Template\Node;
use UKMNorge\Rapporter\Template\Samling;
use UKMNorge\Arrangement\Videresending\Ledere\Ledere;

$handleCall = new HandleAPICall([], [], ['GET', 'POST'], false);

$root = new Node('Root', null);
$nattNodes = []; // Cache: $nattKey => Node

$til = new Arrangement(get_option('pl_id'));

foreach ($til->getVideresending()->getAvsendere() as $avsender) {
    $fra = $avsender->getArrangement();
    $fylke = $fra->getFylke();

    $ledere = new Ledere($fra->getId(), $til->getId());

    foreach ($ledere->getAll() as $leder) {
        if (!in_array($leder->getType(), ['sykerom'])) {
            $lederObj = [
                'id' => $leder->getId(),
                'navn' => $leder->getNavn(),
                'type' => $leder->getTypeNavn(),
                'mobil' => $leder->getMobil(),
                'epost' => $leder->getEpost(),
                'type' => $leder->getType(),
                'godkjent' => ($til->getType() != 'land' ? true : ($leder->getGodkjent() == 1)),
            ];

            foreach ($leder->getNetter()->getAll() as $natt) {
                $day = str_pad($natt->getDag(), 2, '0', STR_PAD_LEFT);
                $month = str_pad($natt->getManed(), 2, '0', STR_PAD_LEFT);
                $year = date('Y');
                $lederObj['sted'] = $natt->getSted();

                $dato = DateTime::createFromFormat('d.m.Y', $day . '.' . $month . '.' . $year);

                $nattDato = $dato->format('d-m');
                $nattKey = 'natt_' . $nattDato;


                // Natt node
                if (!isset($nattNodes[$nattKey])) {
                    $nattNode = new Node('Natt', ['hele_dato' => $dato, 'dato' => $nattDato]);
                    $root->addChild($nattKey, $nattNode);
                    $nattNodes[$nattKey] = $nattNode;
                } else {
                    $nattNode = $nattNodes[$nattKey];
                }

                // LederType under Natt
                $lederTypeNavnOriginal = $leder->getTypeNavn();
                $lederTypeKey = 'ledertype_' . trim(mb_strtolower($lederTypeNavnOriginal));

                if (!isset($nattNode->children[$lederTypeKey])) {
                    $lederTypeNode = new Node('LederType', ['type' => $lederTypeNavnOriginal]);
                    $nattNode->addChild($lederTypeKey, $lederTypeNode);
                } else {
                    $lederTypeNode = $nattNode->children[$lederTypeKey];
                }

                // Fylke under LederType
                $fylkeKey = 'fylke_' . $fylke->getId();

                if (!isset($lederTypeNode->children[$fylkeKey])) {
                    $fylkeNode = new Node('Fylke', $fylke);
                    $lederTypeNode->addChild($fylkeKey, $fylkeNode);
                } else {
                    $fylkeNode = $lederTypeNode->children[$fylkeKey];
                }

                // Arrangement under Fylke
                $arrangementKey = 'arrangement_' . $fra->getId();

                if (!isset($fylkeNode->children[$arrangementKey])) {
                    $arrangementNode = new Node('Arrangement', $fra);
                    $fylkeNode->addChild($arrangementKey, $arrangementNode);
                } else {
                    $arrangementNode = $fylkeNode->children[$arrangementKey];
                }

                // Leder under Arrangement
                $nodeLeder = new Node('Leder', $lederObj);
                $arrangementNode->addChild($leder->getId(), $nodeLeder);
            }
        }
    }
}

$arrRes = [
    'root' => $root,
    'status' => true,
];

$handleCall->sendToClient($arrRes);
