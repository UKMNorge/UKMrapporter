<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Rapporter\Framework\Kategorier;
use UKMNorge\Twig\Twig;

$rapport = UKMrapporter::getAktivRapport();


if (get_option('pl_id')) {
    $arrangement = new Arrangement(intval(get_option('pl_id')));
    UKMrapporter::addViewData('arrangement', $arrangement);

    if ($rapport->kreverHendelse() && sizeof($arrangement->getProgram()->getAbsoluteAll()) == 0) {
        UKMrapporter::setAction('opprettHendelse');
    }
}

Twig::addPath(UKMrapporter::getPluginPath() . 'twig/Components/');
UKMrapporter::addViewData('rapport', $rapport);
