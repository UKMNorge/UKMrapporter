<?php

require_once('overnatting.controller.php');


$TWIG['romtyper'] = $romtyper = UKMF_overnatting_getRomtyper();


/// HOTELL UKM NORGE	
global $objPHPExcel;
$objPHPExcel = null;
exInit('Overnatting hotell UKM Norge');
exSheetName('Ledere');

$rad = 1;
excell('A'.$rad, 'Fra','bold');
excell('B'.$rad, 'Navn','bold');
excell('C'.$rad, 'Mobil','bold');
excell('D'.$rad, 'E-post','bold');
$col = 5;

foreach( $netter as $num => $data ) {
	excell(i2a($col+$num).$rad, date('D d.m',$data->timestamp),'bold');
}

while( $r = SQL::fetch( $res ) ) {
	$rad++;
	$leder = new leder( $r['l_id'] );
	$navn = empty($leder->l_navn) ? 'Leder uten navn' : $leder->l_navn;
	
	excell('A'.$rad, $leder->kommer_fra,'bold');
	excell('B'.$rad, $navn,'bold');
	excell('C'.$rad, $leder->l_mobilnummer);
	excell('D'.$rad, $leder->l_epost);
	
	foreach( $netter as $num => $data ) {
		$pa_hotell = $leder->natt[ $data->dag.'_'.$data->mnd ]->sted == 'hotell';
		excell(i2a($col+$num).$rad, $pa_hotell ? 'x' : '-');
		if( $pa_hotell ) {
			$count[ 'enkelt' ][ 'ledere' ][ $data->dag.'.'.$data->mnd ][ $r['l_id'] ] = true;
			$count[ 'enkelt' ][ 'total'  ][ $data->dag.'.'.$data->mnd ][ $r['l_id'] ] = true;
		}
	}
}

// RESSURSER FRA UKM NORGE
$sql = new SQL("SELECT `p`.`navn`,
					   `p`.`mobil`,
					   `p`.`epost`,
					   `p`.`ankomst`,
					   `p`.`avreise`,
					   `rom`.`id` AS `rom`,
					   `rom`.`type` AS `romtype`,
					   `gruppe`.`navn` AS `gruppe`
				FROM `ukm_festival_overnatting_person` AS `p`
				JOIN `ukm_festival_overnatting_rel_person_rom` AS `rel` ON (`p`.`id` = `rel`.`person_id`)
				JOIN `ukm_festival_overnatting_rom` AS `rom` ON (`rom`.`id` = `rel`.`rom_id`)
				JOIN `ukm_festival_overnatting_gruppe` AS `gruppe` ON (`gruppe`.`id` = `p`.`gruppe`)
				ORDER BY `p`.`gruppe` ASC, `rel`.`rom_id` ASC 
				");
$res = $sql->run();
while( $r = SQL::fetch( $res ) ) {
	$ressurspersoner[ $r['gruppe'] ][] = $r;
}

$excelArk = 0;
if( is_array( $ressurspersoner ))
foreach( $ressurspersoner as $gruppe => $personer ) {

	foreach( $alle_netter as $num => $data ) {
		foreach( $romtyper as $romtype => $kapasitet ) {
			$count[$romtype][$gruppe][ $data->dag.'.'.$data->mnd ] = 0;
			$count[$romtype][$gruppe][ $data->dag.'.'.$data->mnd ] = array();
		}
	}

	$excelArk++;
	$navn = substr($gruppe, 0, 16);
	$objPHPExcel->createSheet($excelArk);
	$objPHPExcel->setActiveSheetIndex($excelArk);
	exSheetName($navn,'f69a9b');

	excell('A1', 'Romnavn (unikt)', 'bold');
	excell('B1', 'Type', 'bold');
	excell('C1', 'Navn', 'bold');
	excell('D1', 'Mobil', 'bold');
	excell('E1', 'E-post', 'bold');
	$col = 6;
	$rad = 1;

	foreach( $alle_netter as $num => $data ) {
		excell(i2a($col+$num).$rad, date('D d.m',$data->timestamp),'bold');
	}
	$rad = 1;
	foreach( $personer as $p ) {
		$rad++;
		excell('A'.$rad, ucfirst(substr($p['romtype'],0,1)). $p['rom']);
		excell('B'.$rad, $p['romtype'],'bold');
		excell('C'.$rad, $p['navn'],'bold');
		excell('D'.$rad, $p['mobil']);
		excell('E'.$rad, $p['epost']);
		$start = $p['ankomst'];
		$stop = $p['avreise'];
		$selector = ' - ';
		$text = '-';
		foreach( $alle_netter as $num => $data ) {
			// Looper fra første til siste dag
			// Starter med -, og gjør dette til vi når startdato
			// Fra og med startdato, kryss av for overnatting
			if( $start == date('d.m',$data->timestamp) )
				$text = 'x';
			// Fra og med sluttdato, fjern kryss for overnatting
			if( $stop == date('d.m',$data->timestamp) ) 
				$text = '-';
			
			// Hvis ledern skal ha overnatting denne natta, tell det.
			if( 'x' == $text ) {
				$count[ $p['romtype'] ][$gruppe][ $data->dag.'.'.$data->mnd ][ $p['rom'] ] = true;
				$count[ $p['romtype'] ]['total'][ $data->dag.'.'.$data->mnd ][ $p['rom'] ] = true;
			}				
			excell(i2a($col+$num).$rad, $text);
		}
	}
}

$TWIG['excel_hotell_norge'] = exWrite($objPHPExcel,'UKMF_Hotell_UKM_Norge');

$TWIG['alle_netter'] = $alle_netter;
$TWIG['count'] = $count;
