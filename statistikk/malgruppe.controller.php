<?php
if($TWIG['monstring']->fylke->id != 3 || $TWIG['stat_type']=='land') {
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
				$malgruppe[ $r['season'] ][ 'Utvikling '. $TWIG['monstring']->fylke->name ] = round( (100/$r['malgruppe'])*$r['deltakere']  ,2);
			}
			
			// SNITT NASJONALT
			$nasjonalQRY = new SQL("SELECT `season`,SUM(`malgruppe`) AS `malgruppe`, SUM(`deltakere`) AS `deltakere`
							   FROM `ukm_statistics_malgruppe`
							   GROUP BY `season`"
							  );
			$nasjonalRES = $nasjonalQRY->run();
			while( $r = mysql_fetch_assoc( $nasjonalRES ) ) {
				$malgruppe[ $r['season'] ][ 'Utvikling nasjonalt' ] = round( (100/$r['malgruppe'])*$r['deltakere']  ,2);
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
				$malgruppe[ $r['season'] ][ 'Utvikling '. $TWIG['monstring']->fylke->name ] = round( (100/$r['malgruppe'])*$r['deltakere']  ,2);
			}
			
			// SNITT NASJONALT
			$nasjonalQRY = new SQL("SELECT `season`,SUM(`malgruppe`) AS `malgruppe`, SUM(`deltakere`) AS `deltakere`
							   FROM `ukm_statistics_malgruppe`
							   GROUP BY `season`"
							  );
			$nasjonalRES = $nasjonalQRY->run();
			while( $r = mysql_fetch_assoc( $nasjonalRES ) ) {
				$malgruppe[ $r['season'] ][ 'Utvikling nasjonalt' ] = round( (100/$r['malgruppe'])*$r['deltakere']  ,2);
			}
	
			// BESTE OG DÅRLIGSTE KOMMUNE
			$topbotQry = new SQL("SELECT `name`,
										 `ukm_statistics_malgruppe`.`k_id`,
										 `season`,
										 SUM(`malgruppe`) AS `malgruppe`, 
										 SUM(`deltakere`) AS `deltakere`
								   FROM `ukm_statistics_malgruppe`
								   LEFT JOIN `smartukm_kommune` AS `k` ON (`k`.`id` = `ukm_statistics_malgruppe`.`k_id`)
								   WHERE `f_id` = '#fylke'
								   GROUP BY `season`, `k_id`
								   ORDER BY `k_id`, `season`",
								 array('fylke' => $TWIG['monstring']->fylke->id)
								);
			$topbotRes = $topbotQry->run();
			while( $r = mysql_fetch_assoc( $topbotRes ) ) {
				$data = array( 'malgruppe' => $r['malgruppe'], 'deltakere' => $r['deltakere']);
				if( $data['malgruppe'] > 0 ) {
					$data['dekningsgrad'] = round((100/$r['malgruppe'])*$r['deltakere'],2);
				} else {
					$data['dekningsgrad'] = null;
				}
				$kommune[ $r['k_id'] ][ $r['season'] ] = $data;
				if($r['season'] != 2009) {
					$TWIG['dekningsgrad'][ utf8_encode($r['name'])][ $r['season'] ] = $data;
				}
				$kommune[ $r['k_id'] ][ 'total' ]['malgruppe'] += $r['malgruppe'];
				$kommune[ $r['k_id'] ][ 'total' ]['deltakere'] += $r['deltakere'];
				$kommunenavn[ $r['k_id'] ] = utf8_encode( $r['name'] );
			}
	
			foreach( $kommune as $k_id => $data ) {
				$tot_malgruppe = $data['total']['malgruppe'];
				$tot_deltakere = $data['total']['deltakere'];
				if( $tot_malgruppe > 0) {
					$dekning   = (100 / $tot_malgruppe ) * $tot_deltakere;
				} else {
					$dekning = 0;
				}
				$dekningsgrad[ "$dekning" ] = $k_id;
			}
			
			$top = max( array_keys( $dekningsgrad) );
			$min = min( array_keys( $dekningsgrad) );
			
			unset( $kommune[ $top ]['total'] );
			unset( $kommune[ $min ]['total'] );
			unset( $kommune[ $top ][2009] );
			unset( $kommune[ $min ][2009] );
			
			foreach( $TWIG['seasons'] as $ssn ) {
				$val = $kommune[ $dekningsgrad[$top] ][ $ssn ];
				if( $val['malgruppe'] > 0)
					$malgruppe[ $ssn ][ $kommunenavn[ $dekningsgrad[$top] ].' (best dekning i perioden)'] = round( (100/$val['malgruppe'])*$val['deltakere'], 2 );
	
				$val = $kommune[ $dekningsgrad[$min] ][ $ssn ];
				if( $val['malgruppe'] > 0)
					$malgruppe[ $ssn ][ $kommunenavn[ $dekningsgrad[$min] ].' (dårligst dekning i perioden)'] = round( (100/$val['malgruppe'])*$val['deltakere'], 2 );
			}
	
			// FJERN 2009
			unset( $malgruppe[2009] );
			// SORTER
			ksort( $malgruppe );
			// "LAGRE"
			$TWIG['statistikk']['fylket']['malgruppe'] = $malgruppe;
	
		} elseif( $TWIG['stat_type'] == 'land') {
			// SNITT NASJONALT
			$nasjonalQRY = new SQL("SELECT `season`,SUM(`malgruppe`) AS `malgruppe`, SUM(`deltakere`) AS `deltakere`
							   FROM `ukm_statistics_malgruppe`
							   GROUP BY `season`"
							  );
			$nasjonalRES = $nasjonalQRY->run();
			while( $r = mysql_fetch_assoc( $nasjonalRES ) ) {
				$malgruppe[ $r['season'] ][ 'Utvikling nasjonalt' ] = round( (100/$r['malgruppe'])*$r['deltakere']  ,2);
			}

			// BESTE OG DÅRLIGSTE FYLKE
			$topbotQry = new SQL("SELECT `f_id`,
										 `name`,
										 `season`,
										 SUM(`malgruppe`) AS `malgruppe`, 
										 SUM(`deltakere`) AS `deltakere`
								   FROM `ukm_statistics_malgruppe`
								   LEFT JOIN `smartukm_fylke` AS `f` ON (`f`.`id` = `f_id`)
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
				$fylkenavn[ $r['f_id'] ] = utf8_encode($r['name']);
							
				if( $r['malgruppe'] > 0 ) {
					$fylke_dekning = round((100/$r['malgruppe'])*$r['deltakere'],2);
				} else {
					$fylke_dekning = null;
				}
				
				if($r['season'] != 2009) {
					$TWIG['dekningsgrad'][ utf8_encode($r['name'])][ $r['season'] ] = $fylke_dekning;
				}
			}
			
			foreach( $fylke as $f_id => $data ) {
				$tot_malgruppe = $data['total']['malgruppe'];
				$tot_deltakere = $data['total']['deltakere'];
				$dekning   = (100 / $tot_malgruppe ) * $tot_deltakere;
				$dekningsgrad[ "$dekning" ] = $f_id;
			}
			
			$top = max( array_keys( $dekningsgrad) );
			$min = min( array_keys( $dekningsgrad) );
			
			unset( $fylke[ $top ]['total'] );
			unset( $fylke[ $min ]['total'] );
			unset( $fylke[ $top ][2009] );
			unset( $fylke[ $min ][2009] );
	
			foreach( $TWIG['seasons'] as $ssn ) {
				$val = $fylke[ $dekningsgrad[$top] ][ $ssn ];
				$malgruppe[ $ssn ][ $fylkenavn[ $dekningsgrad[$top] ].' (best dekning i perioden)'] = round( (100/$val['malgruppe'])*$val['deltakere'], 2 );
	
				$val = $fylke[ $dekningsgrad[$min] ][ $ssn ];
				$malgruppe[ $ssn ][ $fylkenavn[ $dekningsgrad[$min] ].' (dårligst dekning i perioden)'] = round( (100/$val['malgruppe'])*$val['deltakere'], 2 );
			}
			// FJERN 2009
			unset( $malgruppe[2009] );
			// SORTER
			ksort( $malgruppe );
			ksort( $TWIG['dekningsgrad'] );
			
			// "LAGRE"
			$TWIG['statistikk']['nasjonalt']['malgruppe'] = $malgruppe;
	} else {
		$TWIG['error'] = array('header' => 'Beklager, en feil har oppstått',
							   'message' => 'Systemet vet ikke hvem du etterspør statistikk for. Vennligst prøv på nytt. Opplever du samme feil igjen, ta kontakt med UKM Norge');
	}
}