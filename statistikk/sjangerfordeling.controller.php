<?php
if($TWIG['stat_type']=='kommune') {
	foreach($TWIG['kommuner'] as $kommune_name => $kommune_id){
		$raw = array();
		// PERSONER
		$persQry = new SQL("SELECT `season`, `stat`.`bt_id`, `type`.`bt_name`, `subcat`, COUNT(`stat_id`) AS `personer`
							FROM `ukm_statistics` AS `stat`
							LEFT JOIN `smartukm_band_type` AS `type` ON (`type`.`bt_id` = `stat`.`bt_id`)
							WHERE `k_id` = '#kommune'
							GROUP BY `stat`.`bt_id`, `subcat`, `season`",
						   array('kommune' => $kommune_id)
						  );
		$raw = calc_sjangerfordeling( $kommune_id, $TWIG['missing'], $persQry );
		
		$sjangerfordeling = $raw;
		$sjangerfordeling_iar = $raw[ $TWIG['season'] ];
		
		unset( $sjangerfordeling[2009] );
		
		$TWIG['statistikk'][$kommune_id]['sjangerfordeling'] = $sjangerfordeling;
		ksort( $TWIG['statistikk'][$kommune_id]['sjangerfordeling'] );
		
		$TWIG['statistikk'][$kommune_id]['sjangerfordeling_iar'] = $sjangerfordeling_iar;
		
		// SKIP TOTALS @ OVERVIEW
		unset( $TWIG['statistikk'][$kommune_id]['sjangerfordeling_iar']['total'] );
		unset( $TWIG['statistikk'][$kommune_id]['sjangerfordeling_iar']['total_scene'] );
	}
} elseif( $TWIG['stat_type'] == 'fylke' ) {
		$raw = array();
		// PERSONER
		$persQry = new SQL("SELECT `season`, `stat`.`bt_id`, `type`.`bt_name`, `subcat`, COUNT(`stat_id`) AS `personer`
							FROM `ukm_statistics` AS `stat`
							LEFT JOIN `smartukm_band_type` AS `type` ON (`type`.`bt_id` = `stat`.`bt_id`)
							WHERE `f_id` = '#fylke'
							GROUP BY `stat`.`bt_id`, `subcat`, `season`",
						   array('fylke' => $TWIG['monstring']->fylke->id)
						  );
		$raw = calc_sjangerfordeling( 'fylket', $TWIG['missing'], $persQry );
		
		// LEGG TIL PL MISSING FRA KOMMUNENE (calc_sjangerfordeling beregner kun for fylket i sin helhet + fylkesmønstringen)		
		foreach( $TWIG['missing_total_kommuner'] as $ssn => $missing ) {
			$raw[ $ssn ][ 'Diverse' ] += $missing;
			$raw[ $ssn ][ 'total' ] += $missing;
		}		
		$sjangerfordeling = $raw;
		$sjangerfordeling_iar = $raw[ $TWIG['season'] ];
		
		unset( $sjangerfordeling[2009] );
		
		$TWIG['statistikk']['fylket']['sjangerfordeling'] = $sjangerfordeling;
		ksort( $TWIG['statistikk']['fylket']['sjangerfordeling'] );
		
		$TWIG['statistikk']['fylket']['sjangerfordeling_iar'] = $sjangerfordeling_iar;
		
		// SKIP TOTALS @ OVERVIEW
		unset( $TWIG['statistikk']['fylket']['sjangerfordeling_iar']['total'] );
		unset( $TWIG['statistikk']['fylket']['sjangerfordeling_iar']['total_scene'] );
		
} else {
	$TWIG['error'] = array('header' => 'Statistikk ikke tilgjengelig',
						   'message'=> 'En systemfeil gjør at det ikke er mulig å beregne statistikk på denne siden. Kontakt UKM Norge'
						   );
}




function calc_sjangerfordeling( $kommune_id, $missing, $persQry ) {
	$persRes = $persQry->run();
		while( $r = mysql_fetch_assoc( $persRes ) ) {
			if( $r['bt_id'] == 1 && !empty( $r['subcat'] ) ) {
				if( strpos($r['subcat'], 'annet' ) !== false ) {
					$kategori = 'Annet_scene';
				} else {
					$kategori = ucfirst( $r['subcat'] );
				}
				$raw[ $r['season'] ]['total_scene'] += $r['personer'];
			} elseif( $r['bt_id'] == 7 ) {
				$kategori = 'Diverse';
			} else {
				$kategori = utf8_encode($r['bt_name']);
			}
			
			$kategori = str_replace('ø','o', $kategori);
		
			if( isset( $raw[ $r['season'] ][ $kategori ] ) )
				$raw[ $r['season'] ][ $kategori ] += $r['personer'];
			else
				$raw[ $r['season'] ][ $kategori ] += $r['personer'];
				
			$raw[ $r['season'] ]['total'] += $r['personer'];
		}
		
		// ADD MISSING AS "DIVERSE"
		foreach( $missing[ $kommune_id ] as $ssn => $num_missing ) {
			$raw[ $ssn ]['Diverse'] += $num_missing;
			$raw[ $ssn ]['total'] += $num_missing;
		}
	return $raw;
}