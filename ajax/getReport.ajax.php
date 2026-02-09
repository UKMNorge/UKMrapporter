<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Rapporter\Framework\Counter;

require_once('UKM/Autoloader.php');
require_once('UKM/inc/twig-admin.inc.php');

$rapport = UKMrapporter::getAktivRapport($_POST['rapport']);
$rapport->setConfigFromString($_POST['config']);

if (method_exists(get_class($rapport), 'getCustomizerData')) {
    $data = $rapport->getCustomizerData();
    if (is_array($data)) {
        UKMrapporter::addResponseData($data);
    }
}

UKMrapporter::addResponseData(
    [
        'config' => $rapport->getConfig(),
        'rapport' => $rapport,
        'renderData' => $rapport->getRenderData(),
        'fancyCounter' => new Counter("tittelCounter")
    ]
);

if (get_option('pl_id')) {
    UKMrapporter::addViewData(
        [
            'arrangement' => new Arrangement((int) get_option('pl_id')),
            'kreverHendelse' => $rapport->kreverHendelse(),
        ]
    );
}

// BRUKER SØKER HTML-UTGAVEN
if ($_POST['format'] == 'html') {
    // Bruker allerede angitt respons-data (inkludert renderData fra ovenfor)
    UKMrapporter::addResponseData(
        'html',
        TWIG(
            $rapport->getTemplate(),
            array_merge(UKMrapporter::getResponseData(), UKMrapporter::getViewData()),
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
