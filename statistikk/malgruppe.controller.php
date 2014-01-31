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
}