<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Rapporter\Framework\Counter;

require_once('UKM/Autoloader.php');
require_once('UKM/inc/twig-admin.inc.php');

$rapport = UKMrapporter::getAktivRapport($_POST['rapport']);
$rapport->setConfigFromString($_POST['config']);

UKMrapporter::addResponseData(
    [
        'config' => $rapport->getConfig(),
        'rapport' => $rapport,
        'arrangement' => new Arrangement((int) get_option('pl_id')),
        'renderData' => $rapport->getRenderData(),
        'kreverHendelse' => $rapport->kreverHendelse(),
        'fancyCounter' => new Counter("tittelCounter")
    ]
);

#sleep(1);


// BRUKER SØKER HTML-UTGAVEN
if ($_POST['format'] == 'html') {
    // Bruker allerede angitt respons-data (inkludert renderData fra ovenfor)
    UKMrapporter::addResponseData(
        'html',
        TWIG(
            $rapport->getTemplate(),
            UKMrapporter::getResponseData(),
            UKMrapporter::getPluginPath()
        )
    );
}

// BRUKER SØKER EXCEL-UTGAVEN
if ($_POST['format'] == 'excel') {
    UKMrapporter::addResponseData(
        'link',
        $rapport->getExcelFile()
    );
}
// BRUKER SØKER WORD-UTGAVEN
elseif ($_POST['format'] == 'word') {
    UKMrapporter::addResponseData(
        'link',
        $rapport->getWordFile()
    );
}
