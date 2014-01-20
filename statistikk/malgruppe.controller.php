<?php
$raw = array();
$kommune = $TWIG['kommuner'][0]['id'];

$lokalQRY = new SQL("SELECT `season`,`prosent`
				   FROM `ukm_statistics_malgruppe`
				   WHERE `k_id` = '#kommune'",
				   array('kommune' => $kommune)
				  );
$lokalRES = $lokalQRY->run();
while( $r = mysql_fetch_assoc( $lokalRES ) ) {
	$malgruppe[ $r['season'] ][ $TWIG['monstring']->name ] = $r['prosent'];
}

$fylkeQRY = new SQL("SELECT `season`,SUM(`malgruppe`) AS `malgruppe`, SUM(`deltakere`) AS `deltakere`
				   FROM `ukm_statistics_malgruppe`
				   WHERE `f_id` = '#fylke'
				   GROUP BY `season`",
				   array('fylke' => $TWIG['monstring']->fylke->id)
				  );
$fylkeRES = $fylkeQRY->run();
while( $r = mysql_fetch_assoc( $fylkeRES ) ) {
	$malgruppe[ $r['season'] ][ $TWIG['monstring']->fylke->name ] = round( (100/$r['malgruppe'])*$r['deltakere']  ,2);
}

$litenQRY = new SQL("SELECT `season`,SUM(`malgruppe`) AS `malgruppe`, SUM(`deltakere`) AS `deltakere`
				   FROM `ukm_statistics_malgruppe`
				   WHERE `size` = 'liten'
				   GROUP BY `season`"
				  );
$litenRES = $litenQRY->run();
while( $r = mysql_fetch_assoc( $litenRES ) ) {
	$malgruppe[ $r['season'] ][ 'Små kommuner' ] = round( (100/$r['malgruppe'])*$r['deltakere']  ,2);
}

$storQRY = new SQL("SELECT `season`,SUM(`malgruppe`) AS `malgruppe`, SUM(`deltakere`) AS `deltakere`
				   FROM `ukm_statistics_malgruppe`
				   WHERE `size` = 'stor'
				   GROUP BY `season`"
				  );
$storRES = $storQRY->run();
while( $r = mysql_fetch_assoc( $storRES ) ) {
	$malgruppe[ $r['season'] ][ 'Store kommuner' ] = round( (100/$r['malgruppe'])*$r['deltakere']  ,2);
}

$nasjonalQRY = new SQL("SELECT `season`,SUM(`malgruppe`) AS `malgruppe`, SUM(`deltakere`) AS `deltakere`
				   FROM `ukm_statistics_malgruppe`
				   GROUP BY `season`"
				  );
$nasjonalRES = $nasjonalQRY->run();
while( $r = mysql_fetch_assoc( $nasjonalRES ) ) {
	$malgruppe[ $r['season'] ][ 'Nasjonalt' ] = round( (100/$r['malgruppe'])*$r['deltakere']  ,2);
}
unset( $malgruppe[2009] );

$TWIG['stat']['malgruppe'] = $malgruppe;