<?php

use UKMNorge\Rapporter\Template\Samling;
use UKMNorge\Rapporter\Template\Template;

require_once('UKM/Autoloader.php');

$rapport = UKMrapporter::getAktivRapport( $_POST['rapport'] );
$config = Template::loadConfigFromString( $_POST['config'] );

$template = $rapport->filterRenderFile( $config );

UKMrapporter::addResponseData('template', $template);
UKMrapporter::addResponseData('config', $config);