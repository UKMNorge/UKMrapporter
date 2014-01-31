<?php
if($TWIG['stat_type']=='kommune') {
	foreach($TWIG['kommuner'] as $kommune_name => $kommune_id){
		$malgruppe = array();
	
		// DENNE MØNSTRINGEN
		$lokalQRY = new SQL("SELECT `season`,`prosent`
						   FROM `ukm_statistics_malgruppe`
						   WHERE `k_id` = '#kommune'",
						   array('kommune' => $kommune_id)
						  );
		$lokalRES = $lokalQRY->run();
		while( $r = mysql_fetch_assoc( $lokalRES ) ) {
			$malgruppe[ $r['season'] ][ $kommune_name ] = $r['prosent'];
		}
		
		
		// SNITT SMÅ MØNSTRINGER
		$litenQRY = new SQL("SELECT `season`,SUM(`malgruppe`) AS `malgruppe`, SUM(`deltakere`) AS `deltakere`
						   FROM `ukm_statistics_malgruppe`
						   WHERE `size` = 'liten'
						   GROUP BY `season`"
						  );
		$litenRES = $litenQRY->run();
		while( $r = mysql_fetch_assoc( $litenRES ) ) {
			$malgruppe[ $r['season'] ][ 'Snitt små kommuner' ] = round( (100/$r['malgruppe'])*$r['deltakere']  ,2);
		}
		
		
		// SNITT STORE MØNSTRINGER
		$storQRY = new SQL("SELECT `season`,SUM(`malgruppe`) AS `malgruppe`, SUM(`deltakere`) AS `deltakere`
						   FROM `ukm_statistics_malgruppe`
						   WHERE `size` = 'stor'
						   GROUP BY `season`"
						  );
		$storRES = $storQRY->run();
		while( $r = mysql_fetch_assoc( $storRES ) ) {
			$malgruppe[ $r['season'] ][ 'Snitt store kommuner' ] = round( (100/$r['malgruppe'])*$r['deltakere']  ,2);
		}
		
		
		// SNITT I FYLKET
		$fylkeQRY = new SQL("SELECT `season`,SUM(`malgruppe`) AS `malgruppe`, SUM(`deltakere`) AS `deltakere`
						   FROM `ukm_statistics_malgruppe`
						   WHERE `f_id` = '#fylke'
						   GROUP BY `season`",
						   array('fylke' => $TWIG['monstring']->fylke->id)
						  );
		$fylkeRES = $fylkeQRY->run();
		while( $r = mysql_fetch_assoc( $fylkeRES ) ) {
			$malgruppe[ $r['season'] ][ 'Snitt '. $TWIG['monstring']->fylke->name ] = round( (100/$r['malgruppe'])*$r['deltakere']  ,2);
		}
		
		// SNITT NASJONALT
		$nasjonalQRY = new SQL("SELECT `season`,SUM(`malgruppe`) AS `malgruppe`, SUM(`deltakere`) AS `deltakere`
						   FROM `ukm_statistics_malgruppe`
						   GROUP BY `season`"
						  );
		$nasjonalRES = $nasjonalQRY->run();
		while( $r = mysql_fetch_assoc( $nasjonalRES ) ) {
			$malgruppe[ $r['season'] ][ 'Snitt nasjonalt' ] = round( (100/$r['malgruppe'])*$r['deltakere']  ,2);
		}
		
		// FJERN 2009
		unset( $malgruppe[2009] );
		// SORTER
		ksort( $malgruppe );
		// "LAGRE"
		$TWIG['statistikk'][$kommune_id]['malgruppe'] = $malgruppe;
	}
} elseif( $TWIG['stat_type'] == 'fylke' ) {
		// SNITT I FYLKET
		$fylkeQRY = new SQL("SELECT `season`,SUM(`malgruppe`) AS `malgruppe`, SUM(`deltakere`) AS `deltakere`
						   FROM `ukm_statistics_malgruppe`
						   WHERE `f_id` = '#fylke'
						   GROUP BY `season`",
						   array('fylke' => $TWIG['monstring']->fylke->id)
						  );
		$fylkeRES = $fylkeQRY->run();
		while( $r = mysql_fetch_assoc( $fylkeRES ) ) {
			$malgruppe[ $r['season'] ][ 'Snitt '. $TWIG['monstring']->fylke->name ] = round( (100/$r['malgruppe'])*$r['deltakere']  ,2);
		}
		
		// SNITT NASJONALT
		$nasjonalQRY = new SQL("SELECT `season`,SUM(`malgruppe`) AS `malgruppe`, SUM(`deltakere`) AS `deltakere`
						   FROM `ukm_statistics_malgruppe`
						   GROUP BY `season`"
						  );
		$nasjonalRES = $nasjonalQRY->run();
		while( $r = mysql_fetch_assoc( $nasjonalRES ) ) {
			$malgruppe[ $r['season'] ][ 'Snitt nasjonalt' ] = round( (100/$r['malgruppe'])*$r['deltakere']  ,2);
		}

		// BESTE OG DÅRLIGSTE FYLKE
		$topbotQry = new SQL("SELECT `f_id`,
									 `season`,
									 SUM(`malgruppe`) AS `malgruppe`, 
									 SUM(`deltakere`) AS `deltakere`
							   FROM `ukm_statistics_malgruppe`
							   WHERE `f_id` != 3
							   AND	`f_id` < 21
							   GROUP BY `season`, `f_id`
							   ORDER BY `f_id`, `season`",
							 array());
		$topbotRes = $topbotQry->run();
		
		while( $r = mysql_fetch_assoc( $topbotRes ) ) {
			$fylke[ $r['f_id'] ][ $r['season'] ] = array( 'malgruppe' => $r['malgruppe'], 'deltakere' => $r['deltakere'] );
			$fylke[ $r['f_id'] ][ 'total' ]['malgruppe'] += $r['malgruppe'];
			$fylke[ $r['f_id'] ][ 'total' ]['deltakere'] += $r['deltakere'];
		}
		
		foreach( $fylke as $f_id => $data ) {
			$tot_malgruppe = $data['total']['malgruppe'];
			$tot_deltakere = $data['total']['deltakere'];
			$dekning   = (100 / $tot_malgruppe ) * $tot_deltakere;
			$dekningsgrad[ $dekning ] = $f_id;
		}
		
		$top = max( array_keys( $dekningsgrad) );
		$min = min( array_keys( $dekningsgrad) );
		
		unset( $fylke[ $top ]['total'] );
		unset( $fylke[ $min ]['total'] );
		unset( $fylke[ $top ][2009] );
		unset( $fylke[ $min ][2009] );

		foreach( $fylke[ $top ] as $ssn => $val ) {
			$malgruppe[ $ssn ][ 'Snitt beste fylke' ] = round( (100/$val['malgruppe'])*$val['deltakere'], 2 );
		}		
		foreach( $fylke[ $min ] as $ssn => $val ) {
			$malgruppe[ $ssn ][ 'Snitt dårligste fylke' ] = round( (100/$val['malgruppe'])*$val['deltakere'], 2 );
		}		

		// FJERN 2009
		unset( $malgruppe[2009] );
		// SORTER
		ksort( $malgruppe );
		// "LAGRE"
		$TWIG['statistikk']['fylket']['malgruppe'] = $malgruppe;

} else {
	$TWIG['error'] = array('header' => 'Statistikk ikke tilgjengelig',
						   'message'=> 'En systemfeil gjør at det ikke er mulig å beregne statistikk på denne siden. Kontakt UKM Norge'
						   );
}
