<?php
if($TWIG['stat_type']=='kommune') {
	foreach($TWIG['kommuner'] as $kommune_name => $kommune_id){
		$raw = array();
		// INIT ARRAY
		foreach( $TWIG['seasons'] as $ssn ) {
			$raw[ $ssn ] = array('underti' => 0,
								 'tiellevetolv' => 0,
								 'trettenfjorten' => 0,
								 'femtenseksten' => 0,
								 'syttenatten' => 0,
								 'nittentjue' => 0,
								 'overtjue' => 0,
								 'total' => 0);
		}
		// PERSONER
		$persQry = new SQL("SELECT `season`,`age`, COUNT(`stat_id`) AS `personer` 
							FROM `ukm_statistics`
							WHERE `k_id` = '#kommune'
							GROUP BY `age`, `season`",
						   array('kommune' => $kommune_id)
						  );
		$persRes = $persQry->run();
		while( $r = mysql_fetch_assoc( $persRes ) ) {
			$age = (int) $r['age'];
			$group = group( $age );
			
			if( isset( $raw[ $r['season'] ][ $group ] ) )
				$raw[ $r['season'] ][ $group ] += $r['personer'];
			else
				$raw[ $r['season'] ][ $group ] = $r['personer'];
			
			$raw[ $r['season'] ]['total'] += $r['personer'];
		}
	
		unset( $raw[2009] );
		
		ksort( $raw );
		
		$aldersfordeling = $raw;
		$aldersfordeling_total = $raw;
		
		foreach( $aldersfordeling as $ssn => $data ) {
			unset( $data['total'] );		
			$aldersfordeling[$ssn] = $data;
		}
		
		$TWIG['statistikk'][$kommune_id]['aldersfordeling'] = $aldersfordeling;
		$TWIG['statistikk'][$kommune_id]['aldersfordeling_total'] = $aldersfordeling_total;
	}
} elseif( $TWIG['stat_type'] == 'fylke' ) {

} else {
	$TWIG['error'] = array('header' => 'Statistikk ikke tilgjengelig',
						   'message'=> 'En systemfeil gjør at det ikke er mulig å beregne statistikk på denne siden. Kontakt UKM Norge'
						   );
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