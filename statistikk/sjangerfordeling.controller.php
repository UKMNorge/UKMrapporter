<?php
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
	foreach( $TWIG['missing'][ $kommune_id ] as $ssn => $missing ) {
		$raw[ $ssn ]['Diverse'] += $missing;
		$raw[ $ssn ]['total'] += $missing;
	}


	
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