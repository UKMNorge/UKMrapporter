<?php
$raw = array();

$kommune = $TWIG['kommuner'][0]['id'];
	// PERSONER
	$persQry = new SQL("SELECT `season`, `stat`.`bt_id`, `type`.`bt_name`, `subcat`, COUNT(`stat_id`) AS `personer`
						FROM `ukm_statistics` AS `stat`
						LEFT JOIN `smartukm_band_type` AS `type` ON (`type`.`bt_id` = `stat`.`bt_id`)
						WHERE `k_id` = '#kommune'
						GROUP BY `stat`.`bt_id`, `subcat`, `season`",
					   array('kommune' => $kommune)
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
	
$sjangerfordeling = $raw;
$sjangerfordeling_iar = $raw[ $TWIG['season'] ];

unset( $sjangerfordeling[2009] );

$TWIG['stat']['sjangerfordeling'] = $sjangerfordeling;
ksort( $TWIG['stat']['sjangerfordeling'] );

$TWIG['stat']['sjangerfordeling_iar'] = $sjangerfordeling_iar;

// SKIP TOTALS @ OVERVIEW
unset( $TWIG['stat']['sjangerfordeling_iar']['total'] );
unset( $TWIG['stat']['sjangerfordeling_iar']['total_scene'] );