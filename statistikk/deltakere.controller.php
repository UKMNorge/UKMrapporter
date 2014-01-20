<?php
// INIT ARRAY
foreach( $TWIG['seasons'] as $ssn ) {
	$stat_deltakere[ $ssn ] = array('personer' => 'null', 'innslag' => 'null');
}

// "LOOP" KOMMUNER
$kommune = $TWIG['kommuner'][0]['id'];

// PERSONER
$persQry = new SQL("SELECT `season`, 
					COUNT(`stat_id`) AS `personer` 
					FROM `ukm_statistics`
					WHERE `k_id` = '#kommune'
					GROUP BY `season`",
				   array('kommune' => $kommune)
				  );
$persRes = $persQry->run();
while( $r = mysql_fetch_assoc( $persRes ) ) {
	$stat_deltakere[ $r['season'] ]['personer'] = $r['personer'];
}

// INNSLAG
$innQry = new SQL("SELECT `season`, 
				   COUNT(DISTINCT `b_id`) AS `innslag` 
				   FROM `ukm_statistics`
				   WHERE `k_id` = '#kommune'
				   GROUP BY `season`",
				   array('kommune' => $kommune)
				  );
$innRes = $innQry->run();
while( $r = mysql_fetch_assoc( $innRes ) ) {
	$stat_deltakere[ $r['season'] ]['innslag'] = $r['innslag'];
}

// PERSONER PER INNSLAG (PPI)
foreach( $stat_deltakere as $season => $data ) {
	if( $data['personer'] > 0 && $data['innslag'] > 0)
		$stat_deltakere[ $season ]['ppi'] = round( $data['personer'] / $data['innslag'], 2);
	else
		$stat_deltakere[ $season ]['ppi'] = 'null';
}

unset($stat_deltakere[2009]);

// SEND TO TWIG
$TWIG['stat']['deltakere'] = $stat_deltakere;
?>