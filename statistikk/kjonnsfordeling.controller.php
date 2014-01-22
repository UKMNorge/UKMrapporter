<?php
foreach($TWIG['kommuner'] as $kommune_name => $kommune_id){
	$kjonnsfordeling = array();
	// PERSONER
	$persQry = new SQL("SELECT `season`,`sex`, COUNT(`stat_id`) AS `personer` 
						FROM `ukm_statistics`
						WHERE `k_id` = '#kommune'
						GROUP BY `sex`, `season`",
					   array('kommune' => $kommune_id)
					  );
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
		
		if( $data['total'] > 0)
			$oneperson = 100 / $data['total'];
		else 
			$oneperson = 1;
		
		$kjonnsfordeling[ $season ] = array( 'Gutter' => $data[ 'male' ], 'Jenter' => $data[ 'female' ] );
		$kjonnsfordeling_percent[ $season ] = array( 'Gutter' => round( $data['male']*$oneperson, 1), 'Jenter' => round( $data['female']*$oneperson, 1));
	}

	unset( $kjonnsfordeling[2009] );
	unset( $kjonnsfordeling_percent[2009]);
	$TWIG['statistikk'][$kommune_id]['kjonnsfordeling'] = $kjonnsfordeling;
	$TWIG['statistikk'][$kommune_id]['kjonnsfordeling_percent'] = $kjonnsfordeling_percent;
	$TWIG['statistikk'][$kommune_id]['kjonnsfordeling_iar'] = $kjonnsfordeling[ $TWIG['season'] ];
}