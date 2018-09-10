<?php
require_once( 'UKM/inc/excel.inc.php');

$sql = new SQL("SELECT *
				FROM `smartukm_videresending_infoskjema` AS `info`
				JOIN `smartukm_place` AS `place` ON (`info`.`pl_id_from` = `place`.`pl_id`)
				WHERE `info`.`pl_id` = '#place'
				ORDER BY `reise_inn_mate`, `reise_inn_dato` ASC",
				array('place' => get_option('pl_id')) );
$res = $sql->run();

global $objPHPExcel;
$objPHPExcel = null;
exInit('Reise til og fra UKM-Festivalen');
exSheetName('Ankomst');

$objPHPExcel->createSheet(1);
$objPHPExcel->setActiveSheetIndex(1);
exSheetName('Avreise','f69a9b');


$objPHPExcel->setActiveSheetIndex(0);
$rad = 1;
excell('A'.$rad, 'Ankomsttid','bold');
excell('B'.$rad, 'Fylke','bold');
excell('C'.$rad, 'Reisemåte','bold');
excell('D'.$rad, 'Ankomststed','bold');
excell('E'.$rad, 'Ankomstdato','bold');
excell('F'.$rad, 'Totalt ant deltakere','bold');
excell('G'.$rad, 'Kommentarer','bold');
#excell('E'.$rad, 'Antall','bold');
#excell('H'.$rad, 'Alle samtidig?','bold');

$objPHPExcel->setActiveSheetIndex(1);
excell('A'.$rad, 'Avreisetid','bold');
excell('B'.$rad, 'Fylke','bold');
excell('C'.$rad, 'Reisemåte','bold');
excell('D'.$rad, 'Avreisedato','bold');
excell('E'.$rad, 'Totalt ant deltakere','bold');
excell('F'.$rad, 'Kommentarer','bold');
#excell('D'.$rad, 'Antall','bold');
#excell('G'.$rad, 'Alle samtidig?','bold');


while( $r = SQL::fetch( $res ) ) {
	$objPHPExcel->setActiveSheetIndex(0);
 
	$rad++;
	excell('A'.$rad, (string) $r['reise_inn_tidspunkt']);
	excell('B'.$rad, (string) $r['pl_name']);
	excell('C'.$rad, (string) $r['reise_inn_mate']);
	excell('D'.$rad, (string) $r['reise_inn_sted']);
	excell('E'.$rad, (string) $r['reise_inn_dato']);
	excell('F'.$rad, (string) $r['systemet_overnatting_spektrumdeltakere']);
	excell('G'.$rad, (string) $r['reise_inn_samtidig_nei']);
#	excell('E'.$rad, 0);
#	excell('H'.$rad, (string) $r['reise_inn_samtidig']);
	
	$objPHPExcel->setActiveSheetIndex(1);
	excell('A'.$rad, (string) $r['reise_ut_tidspunkt']);
	excell('B'.$rad, (string) $r['pl_name']);
	excell('C'.$rad, (string) $r['reise_ut_mate']);
	excell('D'.$rad, (string) $r['reise_ut_dato']);
	excell('E'.$rad, (string) $r['systemet_overnatting_spektrumdeltakere']);
	excell('F'.$rad, (string) $r['reise_ut_samtidig_nei']);
#	excell('D'.$rad, 0);
#	excell('G'.$rad, (string) $r['reise_ut_samtidig']);
}

$TWIG['excel_reise'] = exWrite($objPHPExcel,'UKMF_Reise_UKMFestivalen');