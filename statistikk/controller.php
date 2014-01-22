<?php
require_once('UKM/monstring.class.php');

$TWIG['season']	= (int)get_option('season');

for($i=2010; $i<=$TWIG['season']; $i++) {
	$TWIG['seasons'][] = $i;
}

$pl = new monstring( get_option( 'pl_id' ) );
$monstring = new stdClass();
$monstring->name = $pl->g('pl_name');
$monstring->season = $pl->g('season');
$monstring->storrelse = 'liten';
$monstring->fellesmonstring = $pl->fellesmonstring();

$monstring->fylke = new StdClass();
$monstring->fylke->name = $pl->g('fylke_name');
$monstring->fylke->id = $pl->g('fylke_id');



$kommuner = $pl->g('kommuner');

foreach( $kommuner as $kommune ) {
	$TWIG['kommuner'][$kommune['name']] = $kommune['id'];
}
ksort($TWIG['kommuner']);

ini_set('display_errors', true);

// MISSING
// LOOP SEASONS
foreach( $TWIG['seasons'] as $ssn ) {	
	// LOOP CURRENT KOMMUNER (ALL WHICH IS PRESENT IN GUI)
	foreach( $TWIG['kommuner'] as $name => $kommune ) {
		
		$monstring = new kommune_monstring( $kommune, $ssn );
		$monstring = $monstring->monstring_get();
		
		$kommuner = $monstring->g('kommuner');
		$num_kommuner = sizeof( $kommuner );
		
		$missing  = $monstring->get('pl_missing');
		
		$my_missing = floor( $missing / $num_kommuner );
		
		$TWIG['missing'][ $kommune ][ $ssn ] = $my_missing;
	}
}



$TWIG['home']	= 'home';

$TWIG['monstring'] = $monstring;

ini_set('display_errors', true);

require_once('deltakere.controller.php');
require_once('sjangerfordeling.controller.php');
require_once('kjonnsfordeling.controller.php');
require_once('malgruppe.controller.php');
require_once('aldersfordeling.controller.php');