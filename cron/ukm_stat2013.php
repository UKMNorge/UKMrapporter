<?php

$time_limit = 1200; // 1200s = 20min
ignore_user_abort(true);
ini_set('max_execution_time', $time_limit);
set_time_limit( $time_limit );

	$DEBUG = isset( $_GET['debug'] );

	if(isset($_GET['season'])) {
		$SEASON = $_GET['season'];
	} else {
		if( (int) date('m') > 10)
			$SEASON = (int) date('Y') + 1;
		else
			$SEASON = (int) date('Y');
	}

	require_once('UKM/monstringer.class.php');
	require_once('UKM/monstring.class.php');
	require_once('UKM/innslag.class.php');
	require_once('UKM/person.class.php');
	require_once('UKM/sql.class.php');
	
	function echoFlush( $string ) {
		if( !isset( $_GET['print'] ) ) {
			return;
		}
		echo $string;
		ob_flush();
		flush();
	}
	
	function find_sex($first_name) {
		$first_name = strtoupper($first_name);
		
		$qry = "SELECT `kjonn` from ukm_navn" .
			  " WHERE `navn` = '" . $first_name ."' ";
		
		$qry = new SQL($qry);
		$res = $qry->run('field','kjonn');
		
		if ($res == null)
			$res = 'unknown';
		
		return $res;
		
	}
	
	
	$monstringer = new monstringer($SEASON);
	
	// Henter alle monstringer fra kommunenivaa
	$monstringer = $monstringer->etter_kommune();
	
	
	$TEST_COUNT = 0;
	while( ($r = mysql_fetch_assoc($monstringer)) && $TEST_COUNT < 10) {
		$monstring = new monstring($r['pl_id']);
		echoFlush('<h2>'. $monstring->g('pl_name') .'</h2>');
		// For hvert innslag i en monstring ...
		foreach ($monstring->innslag() as $innslag_inn) {
			$innslag = new innslag($innslag_inn["b_id"]);
			$innslag->loadGeo();
			echoFlush('<h3>' . $innslag->g('b_name') .'</h3>');
			foreach ($innslag->personer() as $p) { // behandle hver person
				$person = new person($p["p_id"]);
				echoFlush($person->g('p_firstname') .' '. $person->g('p_lastname') .'<br />');
				$age = $person->getAge();
				if($age == '25+') 
					$age = 0;
				
				$first_name = $person->get("p_firstname");
				$first_name = split(" ", str_replace("-", " ", $first_name) );
				$first_name = $first_name[0];
				
				$time = $innslag->get('time_status_8');
				
				if (strlen($time) <= 1) {
					$time = $SEASON."-01-01T00:00:01Z";
				} else {
					$time = date("Y-m-d\TH:i:s\Z" , $innslag->get('time_status_8'));
				}
				$time = str_replace(array('T','Z'),array(' ',''), $time);
				// var_dump($time);
				
				$kommuneID = $innslag->get("kommuneID");
				$fylkeID = $innslag->get("fylkeID");
				
				// PRE 2011 does not contain kommune in database.
				// Fake by selecting first kommune of mønstring
				if(empty($kommuneID)) {
					$kommuneID = $monstring->info['kommuner'][0]['id'];
					$fylkeID = $monstring->get('fylke_id');
				}
				
				$age = $person->getAge($monstring);
				if($age == '25+')
					$age = 25;
				$age = (int) $age;
				
				$stats_info = array(
					"b_id" => $innslag->get("b_id"), // innslag-id
					"p_id" => $person->get("p_id"), // person-id
					"k_id" => $kommuneID, // kommune-id
					"f_id" => $fylkeID, // fylke-id
					"bt_id" => $innslag->get("bt_id"), // innslagstype-id
					"subcat" => $innslag->get("b_kategori"), // underkategori
					"age" => $age, // alder
					"sex" => find_sex($first_name), // kjonn
					"time" =>  $time, // tid ved registrering
					"fylke" => 'false', // dratt pa fylkesmonstring?
					"land" => 'false', // dratt pa festivalen?
					"season" => $SEASON // sesong
				);
				
				// skal lagres i ukm_statistics-tabellen
				// var_dump($stats_info);
				
				// faktisk lagre det 
				$qry = "SELECT * FROM `ukm_statistics`" .
						" WHERE `b_id` = '" . $stats_info["b_id"] . "'" .
						" AND `p_id` = '" . $stats_info["p_id"] . "'" .
						#" AND `k_id` = '" . $stats_info["k_id"] . "'"  .
						" AND `season` = '" . $stats_info["season"] . "'";
				$sql = new SQL($qry);
				
				// echo($sql->debug());

				// Sjekke om ting skal settes inn eller oppdateres
				if (mysql_num_rows($sql->run()) > 0)
					$sql_ins = new SQLins('ukm_statistics', array(
						"b_id" => $stats_info["b_id"], // innslag-id
						"p_id" => $stats_info["p_id"], // person-id
						"k_id" => $stats_info["k_id"], // kommune-id
						"season" => $stats_info["season"], // kommune-id
					) );
				else 
					$sql_ins = new SQLins("ukm_statistics");
				
				// Legge til info i insert-sporringen
				foreach ($stats_info as $key => $value) {
					$sql_ins->add($key, $value);
				}
				echoFlush($sql_ins->debug());
				$sql_ins->run();
				
				if( $DEBUG ) {
					$TEST_COUNT += 1;
				}
			}
		}
	}
	// END WHILE
	// No need to flush - end of script flushes
	echo '<h1>End of script</h1>';
	
	// lol
?>
