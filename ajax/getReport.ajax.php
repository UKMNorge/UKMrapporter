<?php

use UKMNorge\Arrangement\Arrangement;

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
        'kreverHendelse' => $rapport->kreverHendelse()
    ]
);

#sleep(1);


// BRUKER SØKER HTML-UTGAVEN
if ($_POST['format'] == 'html') {
    // Bruker allerede angitt respons-data (inkludert renderData fra ovenfor)
    UKMrapporter::addResponseData(
        'html',
        TWIG(
            'Components/renderRapport.html.twig',
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
