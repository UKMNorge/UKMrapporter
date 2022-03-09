<?php

use UKMNorge\Rapporter\Framework\Kategorier;

$rapport_mappe = UKMrapporter::getPluginPath() .'rapporter/';
$rapport_filer = scandir( $rapport_mappe );

$rapporter = [];
foreach( $rapport_filer as $rapport_fil ) {
    // Ikke gå opp en mappe
    if( in_array( $rapport_fil, ['.','..'])) {
        continue;
    }
    // Dropp alle mapper
    if( is_dir( $rapport_mappe . $rapport_fil ) ) {
        continue;
    }
    // PHPftw
    if( pathinfo($rapport_fil)['extension'] != 'php' ) {
        continue;
    }
    
    $class = 'UKMNorge\Rapporter\\'. str_replace('.php','',$rapport_fil);
    $rapport = new $class();

    if(!method_exists($rapport, 'erSynlig') or (method_exists($rapport, 'erSynlig') and $rapport->erSynlig())) {
        Kategorier::getById( $rapport->getKategori()->getId() )->add( $rapport );
    }
}

UKMrapporter::addViewData('rapporter', $rapporter);
UKMrapporter::addViewData('kategorier', Kategorier::getAll());