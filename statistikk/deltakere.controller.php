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
		ksort( $stat_deltakere );
		
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
	
} elseif( $TWIG['stat_type'] == 'land') {
	$persQry = new SQL("SELECT `season`, 
						COUNT(`stat_id`) AS `personer` 
						FROM `ukm_statistics`
						GROUP BY `season`"
					  );
	// INNSLAG
	$innQry = new SQL("SELECT `season`, 
					   COUNT(DISTINCT `b_id`) AS `innslag` 
					   FROM `ukm_statistics`
					   GROUP BY `season`"
					  );					  
	calc_deltakere( 'nasjonalt', $TWIG['missing'], $persQry );
	calc_innslag( 'nasjonalt', $innQry );
	$stat_deltakere = calc_ppi($stat_deltakere);

	// PREPARE AND SEND TO TWIG
	unset($stat_deltakere[2009]);
	$TWIG['statistikk']['nasjonalt']['deltakere'] = $stat_deltakere;

	// DETALJER FOR FYLKENE
	// PL MISSING
	$missingQry = new SQL("SELECT SUM(`missing`) AS `missing`,
						  		  `season`,
						  		  `f_id`,
						  		  `name`
						  		  FROM `ukm_statistics_missing`
						   JOIN `smartukm_fylke` AS `f` ON (`f`.`id` = `f_id`)
						   GROUP BY `f_id`, `season`
						   ORDER BY `f_id`, `season`");
	$missingRes = $missingQry->run();
	while( $r = mysql_fetch_assoc( $missingRes ) ) {
		$TWIG['monstringer_stacked'][ $r['season'] ][ utf8_encode($r['name']) ] += $r['missing'];
		if( $r['season'] != 2009 ) {
			$TWIG['statistikk_detaljert'][ utf8_encode($r['name']) ][ $r['season'] ]['personer'] = $r['missing'];
		}
	}
	
	// FAKTISKE DELTAKERE
	$registeredQry = new SQL("SELECT `season`, 
							  		 COUNT(`stat_id`) AS `personer`,
							  		 `f_id`,
							  		 `name`
							  FROM `ukm_statistics`
							  JOIN `smartukm_fylke` AS `f` ON (`f`.`id` = `f_id`)
							  GROUP BY `season`, `f_id`
							  ORDER BY `f_id`, `season`");
	$registeredRes = $registeredQry->run();
	while( $r = mysql_fetch_assoc( $registeredRes ) ) {	
		$TWIG['monstringer_stacked'][ $r['season'] ][ utf8_encode($r['name']) ] += $r['personer'];
		if( $r['season'] != 2009 ) {
			$TWIG['statistikk_detaljert'][ utf8_encode($r['name']) ][ $r['season'] ]['personer'] += $r['personer'];
		}
	}
	
	// INNSLAG
	$innQry = new SQL("SELECT `season`, 
					   		  COUNT(DISTINCT `b_id`) AS `innslag`,
					   		  `f_id`,
					   		  `name`
					   FROM `ukm_statistics`
					   JOIN `smartukm_fylke` AS `f` ON (`f`.`id` = `f_id`)
					   WHERE `f_id` < 21
					   GROUP BY `season`, `f_id`"
					  );
	$innRes = $innQry->run();
	while( $r = mysql_fetch_assoc( $innRes ) ) {
		if( $r['season'] != 2009 ) {
			$TWIG['statistikk_detaljert'][ utf8_encode($r['name']) ][ $r['season'] ]['innslag'] = $r['innslag'];
		}
	}
	
	foreach( $TWIG['statistikk_detaljert'] as $sd_fylke => $sd_data ) {
		foreach( $sd_data as $sd_ssn => $sd_tall ) {
			$sd_tall['ppi'] = round( (int) $sd_tall['personer'] / (int) $sd_tall['innslag'], 2);
			$TWIG['statistikk_detaljert'][ $sd_fylke ][ $sd_ssn ] = $sd_tall;
		}
	}
	unset( $TWIG['monstringer_stacked'][2009] );
	ksort( $TWIG['statistikk_detaljert'] );

} else {
	$TWIG['error'] = array('header' => 'Beklager, en feil har oppstått',
						   'message' => 'Systemet vet ikke hvem du etterspør statistikk for. Vennligst prøv på nytt. Opplever du samme feil igjen, ta kontakt med UKM Norge');
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