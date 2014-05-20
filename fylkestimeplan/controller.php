<?php

require_once('UKM/monstring.class.php');
require_once('UKM/forestilling.class.php');
require_once('UKM/innslag.class.php');
require_once('UKM/person.class.php');
require_once('UKM/inc/word.inc.php');


$m = new monstring( get_option('pl_id') );
$alle_inn = $m->innslag();
$all_hen = $m->forestillinger('c_start',false);

foreach( $all_hen as $hen ) {
	$h = new forestilling( $hen['c_id'] );
	$hendelse = new stdClass();
	$hendelse->sortKey = preg_replace("/[^A-Za-z0-9-]/", '', date('Y-m-d-H-i-s',$h->g('c_start')).'-'.$h->g('c_name').'-'. $h->g('c_id'));
	$hendelse->ID = $h->g('c_id');
	$hendelse->navn = $h->g('c_name');
	$hendelse->sted = $h->g('c_place');
	$hendelse->timestamp = $h->g('c_start');
	$hendelse->starter = $h->starter();
	$hendelse->dag = date('l d.m', $hendelse->timestamp);

	

	$oppmote = new stdClass();
	$oppmote->start = (int) $h->g('c_start') - ($h->g('c_before')*60);
	$oppmote->delay = (int) $h->g('c_delay') * 60;

	$hendelse->oppmote = $oppmote;
	
	$hendelser[ $hendelse->ID ] = $hendelse;
}

$alle_innslag = array();

$fylkeSQL = new SQL("SELECT *
				   FROM `smartukm_fylke`
				   WHERE `id` < 21
				   ORDER BY `name` ASC");
$fylkeRES = $fylkeSQL->run();
while( $f = mysql_fetch_assoc( $fylkeRES ) ) {
	$fylke = new stdClass();
	$fylke->navn = utf8_encode( $f['name'] );
	$fylke->ID = $f['id'];
	$fylke->hendelser = array();
	
	$fylker[ $fylke->navn ] = $fylke;
}


				   

foreach( $alle_inn as $inn ) {
	$i = new innslag( $inn['b_id'] );
	$i->loadGeo();
	$personer = $i->personer();
	
	$innslag = new stdClass();
	$innslag->ID = $i->g('b_id');
	$innslag->navn = $i->g('b_name');
	
	foreach( $personer as $pers ) {
		$p = new person( $pers['p_id'] );
		$person = new stdClass();
		$person->ID = $p->g('p_id');
		$person->navn = $p->g('p_firstname') . ' '. $p->g('p_lastname');
		$person->mobil = $p->g('p_phone');
		$innslag->personer[] = $person;
	}
	
	$innslag_hendelser = $i->forestillinger( $m->g('pl_id') );
	
	foreach( $innslag_hendelser as $c_id => $rekkefolge ) {
		$hendelsen = $hendelser[ $c_id ];

		$sortKey = $hendelsen->sortKey;
		
		$innslaget = clone $innslag;
		$innslaget->oppmote = $hendelsen->oppmote->start + (($rekkefolge-1) * $hendelsen->oppmote->delay);
		
		if( !isset( $fylker[ $i->g('fylke_utf8') ]->hendelser[ $sortKey ] ) ) {
			$fylkeObject = new stdClass();
			$fylkeObject->info = $hendelsen;
			$fylkeObject->innslag = array();
			$fylkeObject->innslag[ $rekkefolge ] = $innslaget;

			$fylker[ $i->g('fylke_utf8') ]->hendelser[ $sortKey ] = $fylkeObject;
		} else {
			$fylker[ $i->g('fylke_utf8') ]->hendelser[ $sortKey ]->innslag[ $rekkefolge ] = $innslaget;
		}
	}
}

foreach( $fylker as $fylke ) {
	ksort( $fylke->hendelser );
	
	$current_day = '';
	$section = word_init('Fylkestimeplan '. $fylke->navn);
	foreach( $fylke->hendelser as $hendelse ) {
		if( $current_day != $hendelse->info->dag ) {
			$current_day = $hendelse->info->dag;
			woText($section, $current_day, 'center');
		}
		woText($section, date('l d.m', $hendelse->info->timestamp), 'h1');
		woText($section, $hendelse->info->sted .', '. $hendelse->info->starter, 'p');

		//INNSLAGS-TABELL			
		$tab = $section->addTable(array('align'=>'center'));
		
		foreach( $hendelse->innslag as $rekkefolge => $innslag ) {
			$tab->addRow();
			
			// Navn og rekkefølge
			$c = $tab->addCell(8640);
			woText($c, $innslag->navn .'(nr. '.$rekkefolge.')','bold');
			// Oppmøtetid
			$c = $tab->addCell(2700);
			woText($c, date('l H:i',$innslag->oppmote));
			foreach( $innslag->personer as $person ) {
				// NY RAD
				$tab->addRow();
				
				$c = $tab->addCell(500);
				woText($c, ' ');
				
				$c = $tab->addCell(4500);
				woText($c, $person->navn);
				
				$c = $tab->addCell(3640);
				woText($c, $person->mobil);
				
				$c = $tab->addCell(2700);
				woText($c, '');
			}
		}
	}
	$fylke->word = woWrite('UKMF_Fylkestimeplan_'.preg_replace("/[^A-Za-z0-9-]/", '',$fylke->navn).'_'.date('Y'));
}

$TWIG['fylker'] = $fylker;