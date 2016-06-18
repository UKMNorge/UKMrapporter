<?php
require_once('UKM/inc/excel.inc.php');
require_once('UKM/zip.class.php');
require_once('UKM/vcard.class.php');

global $objPHPExcel;

$band_types = new SQL("SELECT * FROM `smartukm_band_type`");
$band_types = $band_types->run();
while($band_type = mysql_fetch_assoc($band_types))
	$bt[$band_type['bt_id']] = utf8_encode($band_type['bt_name']);

$m = new monstring(get_option('pl_id'));
$innslag = $m->innslag_btid();

/** Vcard som zip */
$zipname = 'UKMfestivalen_deltakere';
$STORAGE = sys_get_temp_dir() .'/'. $zipname .'/';
if( 'ukm.no' == UKM_HOSTNAME ) {
	define('ZIP_WRITE_PATH', '/home/ukmno/public_subdomains/download/zip/');
} else {
	define('ZIP_WRITE_PATH', $STORAGE );
}
$zip = new zip($zipname, true);
$zip->debugMode();

if( !file_exists( $STORAGE ) ) {
	mkdir( $STORAGE, 0777, true );
}


foreach($innslag as $band_type => $bands) {

	$band_type_name = substr($bt[$band_type],0,8);
	
//	$objPHPExcel = null;
	exInit('SVEVE');
	exSheetName( $band_type_name );

	excolwidth('A',30);
	excolwidth('B',13);
	excell('A1','Navn');
	excell('B1','Nummer');
	
	$rad = 1;
	foreach($bands as $band) {
		$inn = new innslag($band['b_id']);
		$inn->videresendte($m->g('pl_id'));

		$deltakere = $inn->personer();
		foreach($deltakere as $deltaker) {
			$rad++;
			excell('A'.$rad,$deltaker['p_firstname'].' '.$deltaker['p_lastname']);
			excell('B'.$rad,$deltaker['p_phone']);
		}

		/** 
		 * Participant vCard (and add to zip)
		 **/
		$inn_v2 = new innslag_v2( $band['b_id'] );
		$kontaktperson_deltar = false;
		foreach( $inn_v2->getPersoner()->getAllVideresendt( get_option('pl_id') ) as $person ) {
			if( $inn_v2->getKontaktperson()->getId() == $person->getId() ) {
				$kontaktperson_deltar = true;
			}
			$cardname = 'UKM_participant_'.$person->getId();

			$card = new stdClass();
			$card->first_name 	= $person->getFornavn();
			$card->last_name 	= $person->getEtternavn();

			$card->title 		= ($kontaktperson_deltar ? 'KONTAKTPERSON og ' : '') . $person->getInstrument();

			$card->home_tel		= $person->getMobil();
			$card->fax_tel 		= $person->getMobil() .'600';
			$card->pager_tel 	= $person->getMobil() .'500';

			$card->email1		= $person->getEpost();


			$card->company		= $inn_v2->getNavn();;
			$card->department	= 'UKM '. $inn_v2->getKommune()->getNavn() .' ('. $inn_v2->getKommune()->getFylke()->getNavn() .')';

			$vcard = new vcard( (array) $card );
			$vcard->build();
			$vcard->store( $STORAGE . $cardname, false );
			// EOVCARD
			$zip->add( $STORAGE . $cardname.'.vcf', $cardname.'.vcf' );
		}
		if( !$kontaktperson_deltar ) {
			$person = $inn_v2->getKontaktperson();
			$cardname = 'UKM_participant_'.$person->getId();

			$card = new stdClass();
			$card->first_name 	= $person->getFornavn();
			$card->last_name 	= $person->getEtternavn();

			$card->title 		= 'KONTAKTPERSON';

			$card->home_tel		= $person->getMobil();
			$card->fax_tel 		= $person->getMobil() .'600';
			$card->pager_tel 	= $person->getMobil() .'500';

			$card->email1		= $person->getEpost();


			$card->company		= $inn_v2->getNavn();;
			$card->department	= 'UKM '. $inn_v2->getKommune()->getNavn() .' ('. $inn_v2->getKommune()->getFylke()->getNavn() .')';

			$vcard = new vcard( (array) $card );
			$vcard->build();
			$vcard->store( $STORAGE . $cardname, false );
			// EOVCARD
			$zip->add( $STORAGE . $cardname.'.vcf', $cardname.'.vcf' );
		}
	}
	
	$excelData = new stdClass();
	$excelData->name = $bt[$band_type];
	$excelData->url = exWrite($objPHPExcel, 'UKMF_Sveveksport_'.preg_replace('/[^A-Za-z0-9-.\/]/', '', $excelData->name));
	
	$TWIG['excel'][] = $excelData;
	$TWIG['zip'] = $zip->compress(); //'http://download.ukm.no/zip/'.$zipname; 

}