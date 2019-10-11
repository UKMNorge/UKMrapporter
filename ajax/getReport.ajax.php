<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Rapporter\Template\Samling;
use UKMNorge\Rapporter\Template\Template;

require_once('UKM/Autoloader.php');
require_once('UKM/inc/twig-admin.inc.php');

$rapport = UKMrapporter::getAktivRapport($_POST['rapport']);
$rapport->setConfigFromString($_POST['config']);

UKMrapporter::addResponseData(
    [
        'config' => $rapport->getConfig(),
        'rapport' => $rapport,
        'arrangement' => new Arrangement((Int)get_option('pl_id'))
    ]
);

// Bruker allerede angitt respons-data
UKMrapporter::addResponseData(
    'renderData',
    $rapport->getRenderData( UKMrapporter::getResponseData())
);

// Bruker allerede angitt respons-data (inkludert renderData fra ovenfor)
UKMrapporter::addResponseData(
    'html',
    TWIG(
        'Components/renderRapport.html.twig',
        UKMrapporter::getResponseData(),
        UKMrapporter::getPluginPath()
    )
);