<?php
if($TWIG['stat_type']=='kommune') {
	foreach($TWIG['kommuner'] as $kommune_name => $kommune_id){
		// PERSONER
		$persQry = new SQL("SELECT `season`,`age`, COUNT(`stat_id`) AS `personer` 
							FROM `ukm_statistics`
							WHERE `k_id` = '#kommune'
							GROUP BY `age`, `season`",
						   array('kommune' => $kommune_id)
						  );
		$aldersfordeling = calc_aldersfordeling( $TWIG['seasons'], $persQry );
		$aldersfordeling_total = $aldersfordeling;
		
		foreach( $aldersfordeling as $ssn => $data ) {
			unset( $data['total'] );		
			$aldersfordeling[$ssn] = $data;
		}
		$TWIG['statistikk'][$kommune_id]['aldersfordeling'] = $aldersfordeling;
		$TWIG['statistikk'][$kommune_id]['aldersfordeling_total'] = $aldersfordeling_total;
	}
} elseif( $TWIG['stat_type'] == 'fylke' ) {
	foreach($TWIG['kommuner'] as $kommune_name => $kommune_id){
		// PERSONER
		$persQry = new SQL("SELECT `season`,`age`, COUNT(`stat_id`) AS `personer` 
							FROM `ukm_statistics`
							WHERE `f_id` = '#fylke'
							GROUP BY `age`, `season`",
						   array('fylke' => $TWIG['monstring']->fylke->id)
						  );
		$aldersfordeling = calc_aldersfordeling( $TWIG['seasons'], $persQry );
		$aldersfordeling_total = $aldersfordeling;
		
		foreach( $aldersfordeling as $ssn => $data ) {
			unset( $data['total'] );		
			$aldersfordeling[$ssn] = $data;
		}
		$TWIG['statistikk']['fylket']['aldersfordeling'] = $aldersfordeling;
		$TWIG['statistikk']['fylket']['aldersfordeling_total'] = $aldersfordeling_total;
	}
} elseif( $TWIG['stat_type'] == 'land' ) {
	$persQry = new SQL("SELECT `season`,`age`, COUNT(`stat_id`) AS `personer` 
						FROM `ukm_statistics`
						GROUP BY `age`, `season`",
					   array('kommune' => $kommune_id)
					  );
	$aldersfordeling = calc_aldersfordeling( $TWIG['seasons'], $persQry );
	$aldersfordeling_total = $aldersfordeling;
	
	foreach( $aldersfordeling as $ssn => $data ) {
		unset( $data['total'] );		
		$aldersfordeling[$ssn] = $data;
	}
	$TWIG['statistikk']['nasjonalt']['aldersfordeling'] = $aldersfordeling;
	$TWIG['statistikk']['nasjonalt']['aldersfordeling_total'] = $aldersfordeling_total;

} else {
	$TWIG['error'] = array('header' => 'Beklager, en feil har oppstått',
						   'message' => 'Systemet vet ikke hvem du etterspør statistikk for. Vennligst prøv på nytt. Opplever du samme feil igjen, ta kontakt med UKM Norge');
}




function calc_aldersfordeling( $seasons, $persQry ) {
	$raw = array();
	// INIT ARRAY
	foreach( $seasons as $ssn ) {
		$raw[ $ssn ] = array('underti' => 0,
							 'tiellevetolv' => 0,
							 'trettenfjorten' => 0,
							 'femtenseksten' => 0,
							 'syttenatten' => 0,
							 'nittentjue' => 0,
							 'overtjue' => 0,
							 'total' => 0);
	}
	$persRes = $persQry->run();
	while( $r = mysql_fetch_assoc( $persRes ) ) {
		$group = group( (int) $r['age'] );
		
		if( isset( $raw[ $r['season'] ][ $group ] ) )
			$raw[ $r['season'] ][ $group ] += $r['personer'];
		else
			$raw[ $r['season'] ][ $group ] = $r['personer'];
		
		$raw[ $r['season'] ]['total'] += $r['personer'];
	}
	unset( $raw[2009] );
	ksort( $raw );
	return $raw;
}




function group( $age ) {
	switch( $age ) {
		case 10:
		case 11:
		case 12:
			$group = 'tiellevetolv';
			break;
		case 13:
		case 14:
			$group = 'trettenfjorten';
			break;
		case 15:
		case 16:
			$group = 'femtenseksten';
			break;
		case 17:
		case 18:
			$group = 'syttenatten';
			break;
		case 19:
		case 20:
			$group = 'nittentjue';
			break;
		default: 
			if( $age < 10 && $age > 0 )
				$group = 'underti';
			else
				$group = 'overtjue';
			break;
	}
	return $group;
}