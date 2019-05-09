<?php
require_once('overnatting.controller.php');

/// LEDERE I SPEKTRUM
global $objPHPExcel;
$objPHPExcel = null;
exInit('Deltakerovernatting ledere UKM Norge');

$index = 0;
foreach( array_reverse($netter) as $natt ) {
	$natt->mnd = str_pad( $natt->mnd, 2, "0", STR_PAD_LEFT );
	$natt->dag = str_pad( $natt->dag, 2, "0", STR_PAD_LEFT );
	$objPHPExcel->createSheet($index);
	$objPHPExcel->setActiveSheetIndex($index);

	exSheetName($natt->dag.'.'.$natt->mnd);
	excell('A1:E1', 'Ledere i deltakerovernattingen '. $natt->dag .'.'. $natt->mnd, 'h1');
	$rad = 2;
	excell('A'.$rad, 'Fylke','bold');
	excell('B'.$rad, 'Navn','bold');
	excell('C'.$rad, 'Mobil','bold');
	excell('D'.$rad, 'E-post','bold');
	excell('E'.$rad, 'Hovedleder','bold');

/*		$sql = new SQL("SELECT * FROM `smartukm_videresending_ledere_natt` AS `natt`
				JOIN `smartukm_videresending_ledere_ny` AS `leder` ON (`leder`.`l_id` = `natt`.`l_id`)
				JOIN `smartukm_place` AS `place` ON (`place`.`pl_id` = `leder`.`pl_id_from`)
				WHERE `sted` = 'deltakere'
				AND `dato` = '#dag_#mnd'
				AND `pl_id_to` = '#monstring'
				ORDER BY `place`.`pl_name` ASC, `leder`.`l_navn` ASC",*/
	$sql = new SQL("SELECT *, 
					`smartukm_fylke`.`name` AS `fylke_navn`,
					(`nattleder`.`id` > 0) AS `is_nattleder` 
				FROM `smartukm_videresending_ledere_natt` AS `natt`
				JOIN `smartukm_videresending_ledere_ny` AS `leder` 
					ON (`leder`.`l_id` = `natt`.`l_id`)
				JOIN `smartukm_place` AS `place` 
					ON (`place`.`pl_id` = `leder`.`pl_id_from`)
				LEFT JOIN `smartukm_fylke` 
					ON (`smartukm_fylke`.`id` = `place`.`pl_fylke`)
				LEFT JOIN `smartukm_videresending_ledere_nattleder` AS `nattleder` 
					ON (`nattleder`.`dato` = '#dag_#mnd' AND `nattleder`.`l_id` = `leder`.`l_id`)
				LEFT JOIN `smartukm_fylke` AS `fylke`
					ON (`fylke`.`id` = `place`.`pl_fylke`)
				WHERE `sted` = 'deltakere'
				AND `natt`.`dato` = '#dag_#mnd'
				AND `pl_id_to` = '#monstring'
				ORDER BY `place`.`pl_name` ASC,
						`is_nattleder` DESC,
						`leder`.`l_navn` ASC",
				array('dag' => $natt->dag, 'mnd' => $natt->mnd, 'monstring' => get_option('pl_id')));
	$res = $sql->run();
	while( $r = SQL::fetch( $res ) ) {
		$rad++;
		if( $r['is_nattleder'] ) {
			$style = 'bold';
		} else {
			$style = 'normal';
		}
		excell('A'.$rad, $r['fylke_navn'], $style);
		excell('B'.$rad, $r['l_navn'], $style);
		excell('C'.$rad, $r['l_mobilnummer'], $style);
		excell('D'.$rad, $r['l_epost'], $style);
		excell('E'.$rad, $r['is_nattleder'] ? 'KONTAKTPERSON I NATT' : '', $style);
	}
}
$TWIG['excel_deltakerovernatting'] = exWrite($objPHPExcel,'UKMF'.get_option('season').'_Deltakerovernatting_UKM_Norge');	

$TWIG['alle_netter'] = $alle_netter;
$TWIG['count']['enkel'] = $count_enkel;
$TWIG['count']['dobbel'] = $count_dobbel;
