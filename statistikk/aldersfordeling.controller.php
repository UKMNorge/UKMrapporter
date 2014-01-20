<?php
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
$kommune = $TWIG['kommuner'][0]['id'];

	// PERSONER
	$persQry = new SQL("SELECT `season`,`age`, COUNT(`stat_id`) AS `personer` 
						FROM `ukm_statistics`
						WHERE `k_id` = '#kommune'
						GROUP BY `age`, `season`",
					   array('kommune' => $kommune)
					  );
	$persRes = $persQry->run();
	while( $r = mysql_fetch_assoc( $persRes ) ) {
		$age = (int) $r['age'];
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

$TWIG['stat']['aldersfordeling'] = $aldersfordeling;
$TWIG['stat']['aldersfordeling_total'] = $aldersfordeling_total;