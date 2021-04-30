<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Rapporter\Framework\Kategorier;
use UKMNorge\Twig\Twig;
use UKMNorge\Rapporter\Template\Samling;

$rapport = UKMrapporter::getAktivRapport();


if (get_option('pl_id')) {
    $arrangement = new Arrangement(intval(get_option('pl_id')));
    UKMrapporter::addViewData('arrangement', $arrangement);

    if ($rapport->kreverHendelse() && sizeof($arrangement->getProgram()->getAbsoluteAll()) == 0) {
        UKMrapporter::setAction('opprettHendelse');
    }
}

// Hvis rapport-template trenger å tilgjengeliggjøre
// ekstra data for rapport-kontrollene
if( method_exists(get_class($rapport), 'getCustomizerData') ) {
    $data = $rapport->getCustomizerData();
    if( !is_array($data)) {
        throw new Exception(
            'Rapporten du prøver å åpne er laget feil. '.
            'Kontakt support@ukm.no'
        );
    }
    UKMrapporter::addViewData($data);
}

$templates = Samling::getFromRapport($_GET['rapport'], $arrangement, get_current_user_id());

Twig::addPath(UKMrapporter::getPluginPath() . 'twig/Components/');
UKMrapporter::addViewData('rapport', $rapport);
UKMrapporter::addViewData('templates', $templates->getAll());