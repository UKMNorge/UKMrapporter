<?php
global $objPHPExcel;
require_once('UKM/inc/excel.inc.php');
require_once('UKM/monstring.class.php');

exorientation('landscape');

$objPHPExcel->getProperties()->setCreator('UKM Norges arrangørsystem');
$objPHPExcel->getProperties()->setLastModifiedBy('UKM Norges arrangørsystem');
$objPHPExcel->getProperties()->setTitle('UKM-rapport Kjøreplan');
$objPHPExcel->getProperties()->setKeywords('UKM-rapporter');

## Sett standard-stil
$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
$objPHPExcel->getDefaultStyle()->getFont()->setSize(12);


$monstring = new monstring_v2(get_option('pl_id'));
$sheet = 0;
foreach ($monstring->getProgram()->getAll() as $hendelse) {
	// Hopp over alt annet enn hoved-forestillingene
	if (strpos($hendelse->getNavn(), 'Forestilling ') !== 0) {
		continue;
	}

	####################################################################################
	## OPPRETTER ARK
	$row = 2;
	if ($sheet > 0) {
		$objPHPExcel->createSheet($sheet);
	}
	$objPHPExcel->setActiveSheetIndex($sheet);
	exSheetName($hendelse->getNavn(), 'A0CF67');

	$row = preshow($row);
	$row = studio($row, '-1');
	$row = studio($row, '-1');
	$row = start($row);

	$nummer = 1;
	foreach ($hendelse->getInnslag()->getAll() as $innslag) {
		// Hopp over konferansierer
		if ($innslag->getType()->getId() == 'konferansier') {
			continue;
		}
		$row = konferansier($row);
		$row = innslag($row, $nummer, $innslag);
		$nummer++;
	}

	$sheet++;
}


exWrite($objPHPExcel, 'UKMF_kjoreplan');
$TWIG['excel_kjoreplan'] = '//download.ukm.no/phpexcel/UKMF_kjoreplan.xlsx';
$TWIG['excel_kjoreplan'] = '//download.ukm.dev/excel/UKMF_kjoreplan.xlsx';

function stil($celle, $navn)
{
	switch ($navn) {
		case 'small':
			$styleArray = [
				'font' => [
					'bold' => true,
					'size' => 10,
				]
			];
			break;

		case 'bold':
			$styleArray = [
				'font' => [
					'bold' => true,
				],
				'alignment' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP
			];
			break;

		case 'header':
			$styleArray = [
				'font' => [
					'bold' => true,
					'size' => 18
				]
			];
			break;

		case 'obs':
			$styleArray = [
				'font' => [
					'bold' => true,
					'color' => [
						'argb' => 'FFFF0000',
					],
				],
			];
			break;
	}
	format($celle, $styleArray);
}

function format($celle, $styleArray)
{
	global $objPHPExcel;
	$objPHPExcel
		->getActiveSheet()
		->getStyle($celle)
		->applyFromArray($styleArray);
}

function farge($celle, $farge)
{
	$styleArray = [
		'fill' => [
			'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
			'startColor' => [
				'argb' => $farge,
			],
			'endColor' => [
				'argb' => $farge,
			],
		],
	];
	format($celle, $styleArray);
}

function start($row)
{
	farge('A' . $row . ':C' . $row, '00FF9900');
	excell(merge('A','D',$row), 'START FORESTILLING', 'header');
	stil(merge('A','D',$row),'header');

	return $row + 1;
}

function konferansier($row)
{
	farge(merge('A','C',$row), '00F3F3F3');

	stil('A' . $row, 'small');
	stil(merge('B','C',$row), 'header');

	exwrap('A' . $row);
	excell('A' . $row, 'OVERGANG');
	excell('B' . $row, '??', 'header');
	excell('C' . $row . ':D' . $row, 'KONFERANSIER', 'header');

	$row++;
	excell(merge('A','B',$row), '');
	excell(merge('C','D',$row), '');

	return $row + 1;
}

function innslag($row, $nummer, $innslag)
{
	$row_start = $row+1;
	farge('A' . $row . ':C' . $row, '00CFE2F3');

	stil('A' . $row . ':C' . $row, 'header');

	excell('A' . $row, $nummer);
	try {
		excell('B' . $row, $innslag->getVarighet()->getCompact(), 'header');
	} catch (Exception $e) {
		excell('B' . $row, '???', 'header');
	}
	excell(merge('C', 'D', $row), $innslag->getNavn(), 'header');

	$row++;
	excell(merge('C', 'D', $row), $innslag->getType()->getNavn() . ' - ' . $innslag->getSjanger(), 'bold');

	$row++;
	excell('C' . $row, 'TITTEL', 'bold');
	$text = '';
	try {
		foreach ($innslag->getTitler()->getAll() as $tittel) {
			$text .= $tittel->getTittel() . "\r\n";
		}
	} catch (Exception $e) {
		$text .= '???' . "\r\n";
	}
	excell('D' . $row, $text);

	$row++;
	excell('C' . $row, 'BESKRIVELSE', 'bold');
	excell('D' . $row, $innslag->getBeskrivelse());

	$row++;
	excell('C' . $row, 'PERSONER', 'bold');
	$text = '';
	foreach ($innslag->getPersoner()->getAll() as $person) {
		$text .= $person->getNavn() . ' - ' . $person->getInstrument() . "\r\n";
	}
	excell('D' . $row, $text);

	$row++;
	excell('C' . $row, 'TEKNISKE BEHOV', 'bold');
	excell('D' . $row, $innslag->getTekniskeBehov());

	// Første celle under header (A:B)
	if( $innslag->getPlayback()->har() ) {
		stil(merge('A','B',$row_start),'obs');	
		excell(merge('A','B',$row_start), 'HAR PLAYBACK');
	} else {
		excell(merge('A','B',$row_start), '');
	}

	// Resterende celler til venstre for innslagsinfo
	excell('A'.($row_start+1).':B'.$row, '');

	// Tom rad etter innslaget
	$row++;
	excell(merge('A','B',$row), '');
	excell(merge('C','D',$row), '');

	return $row + 1;
}

function merge($col_start, $col_stop, $row)
{
	return $col_start . $row . ':' . $col_stop . $row;
}

function studio($row, $nummer)
{
	farge('A' . $row . ':C' . $row, 'FFEAD1DC');

	stil('A' . $row . ':C' . $row, 'header');

	excell('A' . $row, $nummer);
	excell('B' . $row, 'xx:xx', 'header');
	excell('C' . $row . ':D' . $row, 'STUDIO (flytt og gi navn)', 'header');

	$row++;
	excell('C' . $row, 'xx:xx');
	excell('D' . $row, '--');

	$row++;
	excell('C' . $row, 'xx:xx');
	excell('D' . $row, '--');

	$row++;
	excell('C' . $row, 'xx:xx');
	excell('D' . $row, '--');

	return $row + 1;
}

function preshow($row)
{
	farge('A' . $row . ':C' . $row, 'FFEAD1DC');
	stil('A' . $row . ':C' . $row, 'header');

	excell('B' . $row, '15:00', 'header');
	excell('C' . $row . ':D' . $row, 'PRESHOW', 'header');

	$row++;
	excell(merge('C','D',$row), 'Kunst-presentasjon surrer på senterlerrett (2m over bakken!)');

	$row++;
	excell('C' . $row, 'xx:xx');
	excell('D' . $row, 'INNHOLD PÅ SCENE');

	$row++;
	excell('C' . $row, 'xx:xx');
	excell('D' . $row, 'MUSIKK + GRAFIKK');

	$row++;
	excell('C' . $row, 'xx:xx');
	excell('D' . $row, 'INNHOLD PÅ SCENE');

	$row++;
	excell('C' . $row, '01:00');
	excell('D' . $row, 'NEDTELLING');

	return $row + 1;
}
