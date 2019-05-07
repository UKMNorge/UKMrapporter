<?php
require_once( 'UKM/inc/excel.inc.php');

$sql = new SQL("SELECT *
				FROM `smartukm_videresending_infoskjema` AS `info`
				JOIN `smartukm_place` AS `place` ON (`info`.`pl_id_from` = `place`.`pl_id`)
				WHERE `info`.`pl_id` = '#place'
				ORDER BY `pl_name` ASC",
				array('place' => get_option('pl_id')) );
$res = $sql->run();

global $objPHPExcel;
$objPHPExcel = null;
exInit('Tilrettelegging på UKM-Festivalen');

excell('A1', 'Fylke','bold');
excell('B1', 'Bevegelseshemninger','bold');
excell('C1', 'Annet','bold');

$rad = 1;
while( $r = SQL::fetch( $res ) ) {
	$rad++;
	excell('A'.$rad, (string) utf8_encode($r['pl_name']));
	excell('B'.$rad, (string) $r['tilrettelegging_bevegelseshemninger']);
	excell('C'.$rad, (string) $r['tilrettelegging_annet']);
}

$TWIG['excel_tilpasninger'] = exWrite($objPHPExcel,'UKMF_Tilrettelegging_UKMFestivalen2');