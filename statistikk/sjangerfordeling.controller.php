<?php
if($TWIG['stat_type']=='kommune') {
	foreach($TWIG['kommuner'] as $kommune_name => $kommune_id){
	
		//// PERSONER
		$raw = array();
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
		
		// INNSLAG
		$raw = array();
		$persQry = new SQL("SELECT `season`, `stat`.`bt_id`, `type`.`bt_name`, `subcat`, COUNT(DISTINCT `b_id`) AS `personer`
							FROM `ukm_statistics` AS `stat`
							LEFT JOIN `smartukm_band_type` AS `type` ON (`type`.`bt_id` = `stat`.`bt_id`)
							WHERE `k_id` = '#kommune'
							GROUP BY `stat`.`bt_id`, `subcat`, `season`",
						   array('kommune' => $kommune_id)
						  );
		$sjangerfordeling = calc_sjangerfordeling( $kommune_id, $TWIG['missing'], $persQry, true );
		unset( $sjangerfordeling[2009] );
		$TWIG['statistikk'][$kommune_id]['sjangerfordeling_innslag'] = $sjangerfordeling;
		ksort( $TWIG['statistikk'][$kommune_id]['sjangerfordeling_innslag'] );
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
		
			
		// INNSLAG
		$raw = array();
		$persQry = new SQL("SELECT `season`, `stat`.`bt_id`, `type`.`bt_name`, `subcat`, COUNT(DISTINCT `b_id`) AS `personer`
							FROM `ukm_statistics` AS `stat`
							LEFT JOIN `smartukm_band_type` AS `type` ON (`type`.`bt_id` = `stat`.`bt_id`)
							WHERE `f_id` = '#fylke'
							GROUP BY `stat`.`bt_id`, `subcat`, `season`",
						   array('fylke' => $TWIG['monstring']->fylke->id)
						  );
		$sjangerfordeling = calc_sjangerfordeling( 'fylket', $TWIG['missing'], $persQry, true );
		unset( $sjangerfordeling[2009] );
		$TWIG['statistikk']['fylket']['sjangerfordeling_innslag'] = $sjangerfordeling;
		ksort( $TWIG['statistikk']['fylket']['sjangerfordeling_innslag'] );

} elseif( $TWIG['stat_type'] == 'land') {
		$raw = array();
		// PERSONER
		$persQry = new SQL("SELECT `season`, `stat`.`bt_id`, `type`.`bt_name`, `subcat`, COUNT(`stat_id`) AS `personer`
							FROM `ukm_statistics` AS `stat`
							LEFT JOIN `smartukm_band_type` AS `type` ON (`type`.`bt_id` = `stat`.`bt_id`)
							WHERE `f_id` < 21
							GROUP BY `stat`.`bt_id`, `subcat`, `season`"
						  );
		$raw = calc_sjangerfordeling( 'nasjonalt', $TWIG['missing'], $persQry );
		
		$sjangerfordeling = $raw;
		$sjangerfordeling_iar = $raw[ $TWIG['season'] ];
		
		unset( $sjangerfordeling[2009] );
		
		$TWIG['statistikk']['nasjonalt']['sjangerfordeling'] = $sjangerfordeling;
		ksort( $TWIG['statistikk']['nasjonalt']['sjangerfordeling'] );
		
		$TWIG['statistikk']['nasjonalt']['sjangerfordeling_iar'] = $sjangerfordeling_iar;
		
		// SKIP TOTALS @ OVERVIEW
		unset( $TWIG['statistikk']['nasjonalt']['sjangerfordeling_iar']['total'] );
		unset( $TWIG['statistikk']['nasjonalt']['sjangerfordeling_iar']['total_scene'] );


		// INNSLAG
		$raw = array();
		$persQry = new SQL("SELECT `season`, `stat`.`bt_id`, `type`.`bt_name`, `subcat`, COUNT(DISTINCT `b_id`) AS `personer`
							FROM `ukm_statistics` AS `stat`
							LEFT JOIN `smartukm_band_type` AS `type` ON (`type`.`bt_id` = `stat`.`bt_id`)
							WHERE `f_id` < 21
							GROUP BY `stat`.`bt_id`, `subcat`, `season`"
						  );
		$sjangerfordeling = calc_sjangerfordeling( 'nasjonalt', $TWIG['missing'], $persQry, true );
		unset( $sjangerfordeling[2009] );
		$TWIG['statistikk']['nasjonalt']['sjangerfordeling_innslag'] = $sjangerfordeling;
		ksort( $TWIG['statistikk']['nasjonalt']['sjangerfordeling_innslag'] );
		
} else {
	$TWIG['error'] = array('header' => 'Beklager, en feil har oppstått',
						   'message' => 'Systemet vet ikke hvem du etterspør statistikk for. Vennligst prøv på nytt. Opplever du samme feil igjen, ta kontakt med UKM Norge');
}




function calc_sjangerfordeling( $kommune_id, $missing, $persQry, $innslag=false ) {
	$persRes = $persQry->run();
		while( $r = SQL::fetch( $persRes ) ) {
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
		
		if(!$innslag) {
			// ADD MISSING AS "DIVERSE"
			foreach( $missing[ $kommune_id ] as $ssn => $num_missing ) {
				$raw[ $ssn ]['Diverse'] += $num_missing;
				$raw[ $ssn ]['total'] += $num_missing;
			}
		}
	return $raw;
}