<?php
	
require_once( 'UKM/leder.class.php' );
require_once( 'UKM/inc/excel.inc.php');

// LAST INN ALLE FYLKER
	$fylker = new SQL("SELECT `id`
					   FROM `smartukm_fylke`
					   ORDER BY `name` ASC");
	$fylker = $fylker->run();
	
	while($row = SQL::fetch($fylker)) {
		$fylke = new fylke_monstring($row['id'], get_option('season'));
		$fylke = $fylke->monstring_get();
	
		if(!$fylke)	
			continue;
		if(!is_numeric($fylke->g('pl_id')) || $fylke->g('pl_id')==0)
			continue;
	
		$TWIG['fylker'][] = array('name' => $fylke->get('pl_name'),
								 'link' => $fylke->get('link'));
	}

// LAST INN INFO OM FESTIVALEN
	$m = new monstring( get_option('pl_id') );
	$netter = $m->netter();

	require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'controller/overnatting_netter.controller.php');
	krsort($TWIG['netter']['for']);
	$alle_netter = array_merge( $TWIG['netter']['for'], $TWIG['netter']['under'], $TWIG['netter']['etter'] );
	$count_enkel = array();
	$count_dobbel = array();
	foreach( $alle_netter as $num => $data ) {
		$count_enkel['ledere'][ $data->dag.'.'.$data->mnd ] = 0;
		$count_dobbel['ledere'][$data->dag.'.'.$data->mnd ] = array();
		$count_enkel['total'][ $data->dag.'.'.$data->mnd ] = 0;
		$count_dobbel['total'][$data->dag.'.'.$data->mnd ] = array();
	}

// LAST INN INFO OM LEDERE
	$ledere = new SQL("SELECT `l_id`,`sort`.`pl_name`
						FROM `smartukm_videresending_ledere_ny` AS `leder`
						LEFT JOIN `smartukm_place` AS `sort` ON (`sort`.`pl_id` = `leder`.`pl_id_from`)
						WHERE `pl_id_to` = '#pl_to'
						AND `leder`.`season` = '#season'
						ORDER BY `sort`.`pl_name` ASC
						",
					array(	'pl_to' => get_option('pl_id'),
							'season' => get_option('season'),
						)
					);
	$res = $ledere->run();
