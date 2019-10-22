<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Rapporter\Template\Samling;

require_once('UKM/Autoloader.php');

$arrangement = new Arrangement( get_option('pl_id') );

$templates = Samling::getFromRapport( $_POST['rapport'], $arrangement->getId() );

UKMrapporter::addResponseData('templates', $templates->getAll());
