<?php
	
require_once('UKM/fylker.class.php');
require_once('UKM/monstringer.class.php');

$fylker = [];
foreach( fylker::getAll() as $fylke ) {
    $fylke->monstringer_registrerte = [];
    $fylke->monstringer_uregistrerte = [];
    
	$monstringCollection = new monstringer_v2( get_site_option('season') );
	$monstringer = $monstringCollection->utenGjester( $monstringCollection->getAllByFylke( $fylke ) );
	
	foreach( $monstringer as $monstring ) {
		if( $monstring->erRegistrert() ) {
			$fylke->monstringer_registrert[] = $monstring;
        } else {
			$fylke->monstringer_uregistrert[] = $monstring;
		}
	}
	$fylker[ $fylke->navn ] = $fylke;
}

$TWIG['fylker'] = $fylker;