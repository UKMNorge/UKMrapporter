<?php
	
require_once('UKM/fylker.class.php');
require_once('UKM/monstringer.class.php');

$monstringer = [];

for( $i=2011; $i<2015; $i++ ) {
	$monstringCollection = new monstringer_v2( $i );
	foreach( fylker::getAll() as $fylke ) {
		if( !isset( $monstringer[ $fylke->getLink() ] ) ) {
			$monstringer[ $fylke->getLink() ] = new stdClass();
			$monstringer[ $fylke->getLink() ]->info = $fylke;
			$monstringer[ $fylke->getLink() ]->monstringer = [];
		}
		
		$sesongdata = new stdClass();
		$sesongdata->monstringer = $monstringCollection->getAllByFylke( $fylke );
		$sesongdata->count_alle_monstringer = sizeof( $sesongdata->monstringer );
		
		// LOOP ALLE MØNSTRINGER
		$count_registrert = 0;
		$count_uregistrert = 0;
		$count_kommuner_registrert = 0;
		$count_kommuner_uregistrert = 0;
		foreach( $sesongdata->monstringer as $sesong_monstring ) {
			if( $sesong_monstring->erRegistrert() ) {
				$count_registrert++;
				$count_kommuner_registrert += $sesong_monstring->getAntallKommuner();
			} else {
				$count_uregistrert++;
				$count_kommuner_uregistrert += $sesong_monstring->getAntallKommuner();
			}
		}
		
		$sesongdata->registrert = $count_registrert;
		$sesongdata->uregistrert = $count_uregistrert;
		$sesongdata->kommuner_registrert = $count_kommuner_registrert;
		$sesongdata->kommuner_uregistrert = $count_kommuner_uregistrert;

		$monstringer[ $fylke->getLink() ]->monstringer[ $i ] = $sesongdata;	
	}
}

#echo '<pre>';var_dump( $monstringer );echo'</pre>';
$TWIG['fylker'] = $monstringer;