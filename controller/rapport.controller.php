<?php

use UKMNorge\Rapporter\Framework\Kategorier;
use UKMNorge\Twig\Twig;

$rapport = UKMrapporter::getAktivRapport();

Twig::addPath( UKMrapporter::getPluginPath() .'twig/Components/');
UKMrapporter::addViewData('rapport', $rapport);
