<?php

	$SEASON = 2014;
	require_once('UKM/monstringer.class.php');
	require_once('UKM/monstring.class.php');
	require_once('UKM/innslag.class.php');
	require_once('UKM/person.class.php');
	require_once('UKM/sql.class.php');
	
	
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
		// For hvert innslag i en monstring ...
		foreach ($monstring->innslag() as $innslag_inn) {
			$innslag = new innslag($innslag_inn["b_id"]);
			foreach ($innslag->personer() as $p) { // behandle hver person
				$person = new person($p["p_id"]);
				
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
				// var_dump($time);
				
				$kommuneID = $innslag->get("kommuneID");
				$fylkeID = $innslag->get("fylkeID");
				
				// PRE 2011 does not contain kommune in database.
				// Fake by selecting first kommune of mønstring
				if(empty($kommuneID)) {
					$kommuneID = $monstring->info['kommuner'][0]['id'];
					$fylkeID = $monstring->get('fylke_id');
				}
				
				$stats_info = array(
					"b_id" => $innslag->get("b_id"), // innslag-id
					"p_id" => $person->get("p_id"), // person-id
					"k_id" => $kommuneID, // kommune-id
					"f_id" => $fylkeID, // fylke-id
					"bt_id" => $innslag->get("bt_id"), // innslagstype-id
					"subcat" => $innslag->get("b_kategori"), // underkategori
					"age" => $person->getAge(), // alder
					"sex" => find_sex($first_name), // kjonn
					"time" =>  $time, // tid ved registrering
					"fylke" => false, // dratt pa fylkesmonstring?
					"land" => false, // dratt pa festivalen?
					"season" => $SEASON // sesong
				);
				
				// skal lagres i ukm_statistics-tabellen
				// var_dump($stats_info);
				
				// faktisk lagre det 
				$qry = "SELECT * FROM `ukm_statistics`" .
						" WHERE `b_id` = '" . $stats_info["b_id"] . "'" .
						" AND `p_id` = '" . $stats_info["p_id"] . "'" .
						" AND `k_id` = '" . $stats_info["k_id"] . "'"  .
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
				// echo($sql_ins->debug());
				$sql_ins->run();
				
				// $TEST_COUNT += 1;
			}
		}
	}
	// END WHILE
	echo("Script finished \n");

	// lol
?>
