<?php
if($TWIG['stat_type']=='kommune') {
	foreach($TWIG['kommuner'] as $kommune_name => $kommune_id){
		// PERSONER
		$persQry = new SQL("SELECT `season`,`sex`, COUNT(`stat_id`) AS `personer` 
							FROM `ukm_statistics`
							WHERE `k_id` = '#kommune'
							GROUP BY `sex`, `season`",
						   array('kommune' => $kommune_id)
						  );
		$kjonnsfordeling = calc_kjonnsfordeling( $kommune_id, $TWIG['missing'], $persQry );
		
		foreach( $kjonnsfordeling as $ssn => $num ) {
			$total = $num['Gutter'] + $num['Jenter'];
			$oneperson = $total > 0 ? (100 / $total) : 1;
			$kjonnsfordeling_percent[ $ssn ] = array( 'Gutter' => round( $num['Gutter']*$oneperson, 1), 'Jenter' => round( $num['Jenter']*$oneperson, 1));
		}

		unset( $kjonnsfordeling[2009] );
		unset( $kjonnsfordeling_percent[2009]);
		ksort( $kjonnsfordeling );
		ksort( $kjonnsfordeling_percent );

	
		$TWIG['statistikk'][$kommune_id]['kjonnsfordeling'] = $kjonnsfordeling;
		$TWIG['statistikk'][$kommune_id]['kjonnsfordeling_percent'] = $kjonnsfordeling_percent;
		$TWIG['statistikk'][$kommune_id]['kjonnsfordeling_iar'] = $kjonnsfordeling[ $TWIG['season'] ];
	}
} elseif( $TWIG['stat_type'] == 'fylke' ) {
		// PERSONER
		$persQry = new SQL("SELECT `season`,`sex`, COUNT(`stat_id`) AS `personer` 
							FROM `ukm_statistics`
							WHERE `f_id` = '#fylke'
							GROUP BY `sex`, `season`",
						   array('fylke' => $TWIG['monstring']->fylke->id)
						  );
		$kjonnsfordeling = calc_kjonnsfordeling( $kommune_id, $TWIG['missing'], $persQry );

		foreach( $TWIG['missing_total_kommuner'] as $ssn => $missing ) {		
			$distribute = $missing / 2;
		
			$add_boys = floor( $distribute );
			$add_girls= ceil( $distribute );

			$kjonnsfordeling[ $ssn ]['Gutter'] += $add_boys;
			$kjonnsfordeling[ $ssn ]['Jenter'] += $add_girls;
		}
		
		foreach( $kjonnsfordeling as $ssn => $num ) {
			$total = $num['Gutter'] + $num['Jenter'];
			$oneperson = $total > 0 ? (100 / $total) : 1;
			$kjonnsfordeling_percent[ $ssn ] = array( 'Gutter' => round( $num['Gutter']*$oneperson, 1), 'Jenter' => round( $num['Jenter']*$oneperson, 1));
		}

		unset( $kjonnsfordeling[2009] );
		unset( $kjonnsfordeling_percent[2009]);
		ksort( $kjonnsfordeling );
		ksort( $kjonnsfordeling_percent );
			
		$TWIG['statistikk']['fylket']['kjonnsfordeling'] = $kjonnsfordeling;
		$TWIG['statistikk']['fylket']['kjonnsfordeling_percent'] = $kjonnsfordeling_percent;
		$TWIG['statistikk']['fylket']['kjonnsfordeling_iar'] = $kjonnsfordeling[ $TWIG['season'] ];
} elseif( $TWIG['stat_type'] == 'land') {
	// PERSONER
	$persQry = new SQL("SELECT `season`,`sex`, COUNT(`stat_id`) AS `personer` 
						FROM `ukm_statistics`
						WHERE `f_id` < 21
						GROUP BY `sex`, `season`"
					  );
	$kjonnsfordeling = calc_kjonnsfordeling( 'nasjonalt', $TWIG['missing'], $persQry );
	
	foreach( $kjonnsfordeling as $ssn => $num ) {
		$total = $num['Gutter'] + $num['Jenter'];
		$oneperson = $total > 0 ? (100 / $total) : 1;
		$kjonnsfordeling_percent[ $ssn ] = array( 'Gutter' => round( $num['Gutter']*$oneperson, 1), 'Jenter' => round( $num['Jenter']*$oneperson, 1));
	}

	unset( $kjonnsfordeling[2009] );
	unset( $kjonnsfordeling_percent[2009]);
	ksort( $kjonnsfordeling );
	ksort( $kjonnsfordeling_percent );

	$TWIG['statistikk']['nasjonalt']['kjonnsfordeling'] = $kjonnsfordeling;
	$TWIG['statistikk']['nasjonalt']['kjonnsfordeling_percent'] = $kjonnsfordeling_percent;
	$TWIG['statistikk']['nasjonalt']['kjonnsfordeling_iar'] = $kjonnsfordeling[ $TWIG['season'] ];
} else {
	$TWIG['error'] = array('header' => 'Beklager, en feil har oppstått',
						   'message' => 'Systemet vet ikke hvem du etterspør statistikk for. Vennligst prøv på nytt. Opplever du samme feil igjen, ta kontakt med UKM Norge');
}

function calc_kjonnsfordeling( $kommune_id, $missing, $persQry ) {
	$kjonnsfordeling = array();
	$persRes = $persQry->run();
	while( $r = mysql_fetch_assoc( $persRes ) ) {
		$raw[ $r['season'] ][ $r['sex'] ] = $r['personer'];
	}
	
	// FORDEL UKJENTE
	foreach( $raw as $season => $data ) {
		$ukjent = $data['unknown'];
		$distribute = $ukjent / 2;
		
		$add_boys = floor( $distribute );
		$add_girls= ceil( $distribute );
		
		$data['male'] += $add_boys;
		$data['female'] += $add_girls;
		
		$data['total'] = $data['male'] + $data['female'];
				
		$kjonnsfordeling[ $season ] = array( 'Gutter' => $data[ 'male' ], 'Jenter' => $data[ 'female' ] );
	}
	
	foreach( $missing[ $kommune_id ] as $ssn => $num_missing ) {
		if( $num_missing > 0 ) {
			$gutter = ceil( $num_missing / 2 );
			$jenter = floor( $num_missing / 2);
			$kjonnsfordeling[ $ssn ]['Gutter'] += $gutter;
			$kjonnsfordeling[ $ssn ]['Jenter'] += $jenter;
		}
	}
	
	return $kjonnsfordeling;
}