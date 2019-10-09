<?php

use UKMNorge\Rapporter\Framework\Kategorier;
use UKMNorge\Twig\Twig;

$class = 'UKMNorge\Rapporter\\' . basename($_GET['rapport']);
$rapport = new $class();

Twig::addPath( UKMrapporter::getPluginPath() .'twig/Components/');
UKMrapporter::addViewData('rapport', $rapport);
