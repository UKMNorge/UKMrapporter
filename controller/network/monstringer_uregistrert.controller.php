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
			$fylke->monstringer_registrerte[] = $monstring;
        } else {
			$fylke->monstringer_uregistrerte[] = $monstring;
		}
	}
	$fylker[ $fylke->getNavn() ] = $fylke;
}

$TWIG['fylker'] = $fylker;