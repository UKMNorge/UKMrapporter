<?php

use UKMNorge\OAuth2\HandleAPICall;
use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Arrangement\Videresending\Ledere\Ledere;
use UKMNorge\Arrangement\UKMFestival;
use UKMNorge\Geografi\Fylker;



$handleCall = new HandleAPICall([], [], ['GET', 'POST'], false);

$til = new Arrangement(get_option('pl_id'));

$arrangementer = [];
$netter = [];
$kommentarer = [];
$alleGyldigeNetter = [];
$alleLedere = 0;


if($til->getEierType() == 'land') {
    $til = new UKMFestival($til->getId());
    UKMrapporter::addViewData('overnattingGrupper', $til->getOvernattingGrupper());
}


// Fylker
$selectedFylker = [];
// hvis brukeren velger ingen av alternativene så skal alle fylker vises
    $selectedFylker = Fylker::getAll();

// // Valgte fylker
// foreach($this->getConfig()->getAll() as $selectedItem) {
//     if($selectedItem->getId() == 'vis_fylke_alle') {
//         $selectedFylker = [];
//         $selectedFylker = Fylker::getAll();
//         break;
//     }
//     try{
//         if(strpos($selectedItem->getId(), 'vis_fylke') !== false) {
//             // struktur av strengen er: 'vis_fylke_00'
//             $fylkeId = (int) explode("_", $selectedItem->getId(), 3)[2];
//             $selectedFylker[] = Fylker::getById($fylkeId);
//         }
//     }catch(Exception $e) {
//         throw new Exception(
//             'Beklager, fylke kunne ikke leses ' . $selectedItem->getId(),
//             100001004
//         );
//     }
// }

$arrangementer = [];
$netter = [];
$kommentarer = [];
$alleGyldigeNetter = [];
$alleLedere = 0;

foreach($til->getNetter() as $natt) {
    $alleGyldigeNetter[$natt->format('d_m')] = $natt;
}


// Alle arrangementer som ble videresend til $til
foreach($til->getVideresending()->getAvsendere() as $avsender) {
    $fra = $avsender->getArrangement();
    $kommentarer[$fra->getFylke()->getId()][$fra->getId()] = $fra->getMetaValue('kommentar_overnatting_til_' . $til->getId());
    
    // For alle fylker som ble valgt
    foreach($selectedFylker as $fylke) {
        $fylker[$fylke->getId()] = $fylke;                

        // Hvis $fra (arrangement som ble videresendt) er fra fylke som er valgt
        if($fylke->getId() == $fra->getFylke()->getId()) {
            $arrangementer[$fra->getId()] = $fra;
            
            $ledere = new Ledere($fra->getId(), $til->getId());

            foreach($ledere->getAll() as $leder) {
            
                foreach($leder->getNetter()->getAll() as $natt) {
                    // Hvis natten er del av gyldige netter for 'til' arrangementet og sted er hotell
                    if($alleGyldigeNetter[$natt->getId()] && $natt->getSted() == 'hotell') {
                        // Bare ledere som overnatter i landsbyen skal bli med i rapporten
                        $netter[$natt->getId()]['fylker'][$fylke->getId()][$fra->getId()][] = $leder;
                        $alleLedere++;
                    }
                }
            }
        }
    }
}

var_dump($netter);
?>
