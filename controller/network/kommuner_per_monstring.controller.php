<?php
	
require_once('UKM/fylker.class.php');
require_once('UKM/monstringer.class.php');

$monstringer = [];

$TWIG['startyear'] = 2010;
$TWIG['stopyear'] = get_site_option('season');
for( $i=$TWIG['startyear']; $i<=$TWIG['stopyear']; $i++ ) {
	$monstringCollection = new monstringer_v2( $i );
	
	$sesong = new stdClass();
	$sesong->year = $i;
	$sesong->monstringer = $monstringCollection->utenGjester( $monstringCollection->getAllBySesong() );
	
	// LOOP ALLE MØNSTRINGER
	$count_registrert = 0;
	$count_uregistrert = 0;
	$count_kommuner_registrert = 0;
	$count_kommuner_uregistrert = 0;
	foreach( $sesong->monstringer as $monstring ) {
		if( 'kommune' != $monstring->getType() ) {
			continue;
		}
		if( $monstring->erRegistrert() ) {
			$count_registrert++;
			$count_kommuner_registrert += $monstring->getAntallKommuner();
		} else {
			$count_uregistrert++;
			$count_kommuner_uregistrert += $monstring->getAntallKommuner();
		}
		$sesong->kommuner += $monstring->getAntallKommuner();
	}
	
	$sesong->monstringer = $count_registrert;
	$sesong->uregistrert = $count_uregistrert;
	$sesong->kommuner_registrert = $count_kommuner_registrert;
	$sesong->kommuner_uregistrert = $count_kommuner_uregistrert;

	$monstringer[ $i ] = $sesong;
}

$kommuner = new SQL("SELECT COUNT(`id`) AS `kommuner`
					FROM `smartukm_kommune`
					WHERE `idfylke` < 21
					AND `id` != CONCAT(`idfylke`, '90')
					");
$TWIG['kommuner'] = $kommuner->run('field','kommuner');


$TWIG['sesonger'] = $monstringer;