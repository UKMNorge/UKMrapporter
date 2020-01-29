<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Rapporter\Framework\Kategorier;
use UKMNorge\Twig\Twig;

$rapport = UKMrapporter::getAktivRapport();
$arrangement = new Arrangement(get_option('pl_id'));

Twig::addPath( UKMrapporter::getPluginPath() .'twig/Components/');
UKMrapporter::addViewData('rapport', $rapport);
UKMrapporter::addViewData('arrangement', $arrangement);

if( $rapport->kreverHendelse() && sizeof($arrangement->getProgram()->getAbsoluteAll()) == 0 ) {
    UKMrapporter::setAction('opprettHendelse');
}