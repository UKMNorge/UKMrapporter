<?php
global $stat_deltakere, $stat_deltakere_fylke;

// INIT ARRAY
foreach( $TWIG['seasons'] as $ssn ) {
	$stat_deltakere[ $ssn ] = array('personer' => 'null', 'innslag' => 'null');
}

if( $TWIG['stat_type'] == 'kommune' ) {
	foreach($TWIG['kommuner'] as $kommune_name => $kommune_id){
		$stat_deltakere = array();
	
		// PERSONER
		$persQry = new SQL("SELECT `season`, 
							COUNT(`stat_id`) AS `personer` 
							FROM `ukm_statistics`
							WHERE `k_id` = '#kommune'
							GROUP BY `season`",
						   array('kommune' => $kommune_id)
						  );
		// INNSLAG
		$innQry = new SQL("SELECT `season`, 
						   COUNT(DISTINCT `b_id`) AS `innslag` 
						   FROM `ukm_statistics`
						   WHERE `k_id` = '#kommune'
						   GROUP BY `season`",
						   array('kommune' => $kommune_id)
						  );					  
	
		calc_deltakere( $kommune_id, $TWIG['missing'], $persQry );
		calc_innslag( $kommune_id, $innQry );
		$stat_deltakere = calc_ppi($stat_deltakere);
	
		// PREPARE AND SEND TO TWIG
		unset($stat_deltakere[2009]);
		$TWIG['statistikk'][$kommune_id]['deltakere'] = $stat_deltakere;
	}
} elseif( $TWIG['stat_type'] == 'fylke' ) {
	$stat_deltakere_fylke = array();
	foreach($TWIG['kommuner_i_fylket'] as $kommune_name => $kommune_id){
		$stat_deltakere = array();
	
		// PERSONER
		$persQry = new SQL("SELECT `season`, 
							COUNT(`stat_id`) AS `personer` 
							FROM `ukm_statistics`
							WHERE `k_id` = '#kommune'
							GROUP BY `season`",
						   array('kommune' => $kommune_id)
						  );
		// INNSLAG
		$innQry = new SQL("SELECT `season`, 
						   COUNT(DISTINCT `b_id`) AS `innslag` 
						   FROM `ukm_statistics`
						   WHERE `k_id` = '#kommune'
						   GROUP BY `season`",
						   array('kommune' => $kommune_id)
						  );					  
	
		calc_deltakere( $kommune_id, $TWIG['missing'], $persQry );
		calc_innslag( $kommune_id, $innQry );

		$stat_deltakere = calc_ppi($stat_deltakere);
		// PREPARE AND SEND TO TWIG
		unset($stat_deltakere[2009]);
		$TWIG['statistikk_detaljert'][$kommune_id]['deltakere'] = $stat_deltakere;

		foreach( $TWIG['seasons'] as $ssn ) {
			$TWIG['monstringer_stacked'][ $ssn ][ $kommune_name ] = $stat_deltakere[ $ssn ]['personer'];
		}
#		$TWIG['monstringer_stacked'][ 0 ][ $kommune_name ] = $kommune_name;
	
	}
	
	// ADD MISSING AT FYLKE LEVEL
	if( isset( $TWIG['missing']['fylket'] ) ) {
		foreach( $TWIG['missing']['fylket'] as $ssn => $missing ) {
			$stat_deltakere_fylke[ $ssn ]['personer'] += $missing;
		}
	}
	$stat_deltakere_fylke = calc_ppi($stat_deltakere_fylke);
	
	// PREPARE AND SEND TO TWIG
	unset($stat_deltakere_fylke[2009]);
	$TWIG['statistikk']['fylket']['deltakere'] = $stat_deltakere_fylke;
	
	ksort( $TWIG['monstringer_stacked'] );
	
} else {

}




////////////////////////////////////////////////////////////////////////////////////
//									FUNCTIONS
////////////////////////////////////////////////////////////////////////////////////
function calc_deltakere( $kommune_id, $missing, $persQry ) {
	global $stat_deltakere, $stat_deltakere_fylke;
		
	$persRes = $persQry->run();
	while( $r = mysql_fetch_assoc( $persRes ) ) {
		$stat_deltakere[ $r['season'] ]['personer'] = $r['personer'];
		$stat_deltakere_fylke[ $r['season'] ]['personer'] += $r['personer'];
	}
	
	if( isset( $missing[ $kommune_id ] ) ) {
		foreach( $missing[$kommune_id] as $ssn => $num_missing ) {
			$stat_deltakere[ $ssn ]['personer'] += $num_missing;
			$stat_deltakere_fylke[ $ssn ]['personer'] += $num_missing;
		}
	}
}


function calc_innslag( $kommune_id, $innQry ) {
	global $stat_deltakere, $stat_deltakere_fylke;
		
	$innRes = $innQry->run();
	while( $r = mysql_fetch_assoc( $innRes ) ) {
		$stat_deltakere[ $r['season'] ]['innslag'] = $r['innslag'];
		$stat_deltakere_fylke[ $r['season'] ]['innslag'] += $r['innslag'];
	}
}

function calc_ppi($dataarray) {
	// PERSONER PER INNSLAG (PPI)
	foreach( $dataarray as $season => $data ) {
		if( $data['personer'] > 0 && $data['innslag'] > 0)
			$dataarray[ $season ]['ppi'] = round( $data['personer'] / $data['innslag'], 2);
		else
			$dataarray[ $season ]['ppi'] = 'null';
	}
	return $dataarray;
}
?>