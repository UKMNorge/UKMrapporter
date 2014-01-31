<?php
require_once('UKM/monstring.class.php');
ini_set('display_errors', true);

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
$TWIG['monstring'] = $monstring;


if( get_option('site_type') == 'kommune' ) {
	$TWIG['stat_type'] = 'kommune';

	$kommuner = $pl->g('kommuner');
	
	foreach( $kommuner as $kommune ) {
		$TWIG['kommuner'][$kommune['name']] = $kommune['id'];
		$TWIG['statistikk'][ $kommune['id'] ]['metadata'] = array('id' => $kommune['id'], 'name' => $kommune['name'] );
	
	}
	ksort($TWIG['kommuner']);
	$TWIG = calc_missing( $TWIG, $TWIG['kommuner'] );

} elseif( get_option('site_type') == 'fylke' ) {
	$TWIG['stat_type'] = 'fylke';

	$TWIG['kommuner']['fylket'] = 'fylket';
	
	$kommuneQry = new SQL("SELECT * FROM `smartukm_kommune`
						   WHERE `idfylke` = '#fylke'
						   ORDER BY `name` ASC",
						   array('fylke' => $monstring->fylke->id)
						  );
	$kommuneRes = $kommuneQry->run();
	while( $r = mysql_fetch_assoc( $kommuneRes ) ) {
		$kommuner_i_fylket[ utf8_encode($r['name']) ] = $r['id'];
		$TWIG['statistikk_detaljert'][ $r['id'] ]['metadata'] = array('id' => $r['id'], 'name' => utf8_encode($r['name']) );
	}
	
	$TWIG['kommuner_i_fylket'] = $kommuner_i_fylket;
	$TWIG['kommuner']['fylket'] = $monstring->fylke->id;

	$TWIG = calc_missing( $TWIG, $TWIG['kommuner_i_fylket'], $monstring->fylke->id );
}

$TWIG['home']	= 'home';

require_once('deltakere.controller.php');
require_once('sjangerfordeling.controller.php');
require_once('kjonnsfordeling.controller.php');
require_once('malgruppe.controller.php');
require_once('aldersfordeling.controller.php');



////////////////////////////////////////////////////////////////////////////////////
//									FUNCTIONS
////////////////////////////////////////////////////////////////////////////////////
function calc_missing( $TWIG, $kommune_array, $fylke=false ) {
	// LOOP SEASONS
	foreach( $TWIG['seasons'] as $ssn ) {	
		// LOOP CURRENT KOMMUNER (ALL WHICH IS PRESENT IN GUI)
		foreach( $kommune_array as $name => $kommune ) {
			
			$monstring = new kommune_monstring( $kommune, $ssn );
			$monstring = $monstring->monstring_get();
			
			$kommuner = $monstring->g('kommuner');
			$num_kommuner = sizeof( $kommuner );
			
			$missing  = $monstring->get('pl_missing');
			
			$my_missing = floor( $missing / $num_kommuner );
			
			$TWIG['missing'][ $kommune ][ $ssn ] = $my_missing;
			$TWIG['missing_total_kommuner'][ $ssn ] += $my_missing;
		}
		if( $fylke ) {
			$monstring = new fylke_monstring( $fylke, $ssn );
			$monstring = $monstring->monstring_get();
			$missing = $monstring->get('pl_missing');
			$TWIG['missing']['fylket'][ $ssn ] = $missing;
		}
	}
	return $TWIG;
}

