<?php
require_once( 'UKM/inc/excel.inc.php');
require_once('UKM/monstring.class.php');
require_once('UKM/allergener.class.php');

$monstring = new monstring_v2( get_option('pl_id') );
$TWIG['monstring'] = $monstring;

if( !isset($_GET['gruppe'] ) ) {
	$TWIG['view'] = 'hendelser';
} else {
	$TWIG['view'] = 'liste';

	/** FINN PERSONER HER */
	if( $_GET['gruppe'] == 'alle' ) {
		$collection = $monstring;
	} elseif( is_numeric( $_GET['gruppe'] ) ) {
		$collection = $monstring->getProgram()->get( $_GET['gruppe'] );
	}

	// SETUP SENSITIVT-REQUESTER
	require_once('UKM/Sensitivt/Sensitivt.php');
	require_once('UKM/Sensitivt/Requester.php');
	$requester = new UKMNorge\Sensitivt\Requester(
		'wordpress', 
		wp_get_current_user()->ID,
		get_option('pl_id')
	);
	UKMNorge\Sensitivt\Sensitivt::setRequester( $requester );

	// EXCEL INIT
	global $objPHPExcel;
	$objPHPExcel = null;
	exInit('Allergier og intoleranser på UKM-Festivalen');
	
	// HEADER
	excell('A1', 'Fylke','bold');
	excell('B1', 'Innslag','bold');
	excell('C1', 'Navn','bold');
	excell('D1', 'Kommentar', 'bold');
	$col_start = 4;

	$col = $col_start;
	foreach( Allergener::getAll() as $allergen ) {
		$col++;
		excell(i2a($col).'1', $allergen->getNavn(), 'bold');
	}
	
	// RADER
	$row = 2;
	$count = 0;
	foreach( $collection->getInnslag()->getAll() as $innslag ) {
		foreach( $innslag->getPersoner()->getAll() as $person ) {
			if( !$person->getSensitivt( $requester )->getIntoleranse()->har() ) {
				continue;
			}
			$col = $col_start;
			$row++;
			excell('A'.$row, $innslag->getKommune()->getFylke());
			excell('B'.$row, $innslag->getNavn());
			excell('C'.$row, $person->getNavn());
			excell('D'.$row, $person->getSensitivt( $requester )->getIntoleranse()->getTekst());
			foreach( Allergener::getAll() as $allergen ) {
				$col++;
				if( $person->getSensitivt( $requester )->getIntoleranse()->harDenne($allergen->getId())) {
					excell(i2a($col).$row, 'X', 'bold');
					$count++;
				} else {
					excell(i2a($col).$row, '-');
				}
			}
		}
	}
	
	$maxrow = $row;
	excell('A2:D2', 'Antall', 'bold');
	$col = $col_start;
	$row = 2;
	foreach( Allergener::getAll() as $allergen ) {
		$col++;
		excell(i2a($col).'2', '=COUNTIF('. i2a($col).'3:'. i2a($col). ($maxrow) .',"X")', 'bold');
	}
	
	
	$TWIG['collection'] = $collection;
	$TWIG['count'] = $count;
	$TWIG['excel_allergier'] = exWrite($objPHPExcel,'UKMF_Allergier_UKMFestivalen');
}