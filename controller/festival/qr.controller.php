<?php
#error_reporting(E_ALL);
require_once('UKM/zip.class.php');	
require_once('UKM/monstring.class.php');
require_once(PLUGIN_DIR .'vendor/autoload.php');

use PHPQRCode\QRcode;
use PHPQRCode\Constants;

$qr_path = DOWNLOAD_PATH .'qr/';

// Hvis mappen ikke eksisterer, opprett
if( !file_exists( $qr_path ) && is_writable( dirname( $qr_path ) ) ) {
	@mkdir( $qr_path, 0777 );
}

// Hvis mappen ikke er skrivbar (eller ikke eksisterer)
if( !is_writable( $qr_path ) ) {
	die(
		'<h2>Beklager, kan ikke generer QR-koder nå. Kontakt UKM Norge</h2>'.
		'<code>'. $qr_path .'</code>'
	);
}


$qrkoder = [];
$zipfiles = [];

$monstring = new monstring_v2( get_option('pl_id') );
if( $monstring->getInnslag()->getAntall() > 0 ) {
	/**
	 * ALFABETISK PAKKE
	**/
	$zipnavn = mb_strtolower( 
		preg_replace(
			'/[^\da-z_-]/i', 
			'', 
			$monstring->getSesong() .'_'. $monstring->getNavn() .'___'. 'Alfabetisk'
		)
	);
	$zip = new zip( $zipnavn, true );
	// Loop alle innslag og lag QR-koder
	foreach( $monstring->getInnslag()->getAll() as $innslag ) {
		$qr = new stdClass();
		$qr->filename 	= $monstring->getId() .'-'. $innslag->getId() .'.png';
		$qr->full_path 	= $qr_path . $qr->filename;
		$qr->link		= 'https://' . $monstring->getLink() . 'pameldte/'. $innslag->getId() .'/';

		$qrkoder[ $innslag->getId() ] = $qr;
		
		// PNG-utgave
		@unlink( $qr->full_path);
		QRcode::png(
			$qr->link, 			// STRING text string to encode
			$qr->full_path, 	// STRING output file name, if false outputs to browser with required headers
			'Q', 				// STRING error correction level L, M, Q or H
			300, 				// INT pixel size, multiplier for each 'virtual' pixel
			2,					// INT code margin (silent zone) in 'virtual' pixels
			false				// BOOL true: code is outputed to browser and saved to file. false: only saved to file
		);
		
		try {
			$addRes = $zip->add(
				$qr->full_path,
				$innslag->getNavn().'.png'
			);
		} catch( Exception $e ) {
			
		}
	}
	$zipfile = new stdClass();
	$zipfile->navn = 'Alfabetisk';
	$zipfile->url = $zip->compress();
	$TWIG['zip_alfabetisk'] = $zipfile;
	
	
	/**
	 * LOOP ALLE FORESTILLINGER OG LAG FORESTILLINGSPAKKER
	**/
	foreach( $monstring->getProgram()->getAllInkludertSkjulte() as $hendelse ) {
		$zipnavn = mb_strtolower( 
			preg_replace(
				'/[^\da-z_-]/i', 
				'', 
				$monstring->getSesong() .'_'. $monstring->getNavn() .'__'. $hendelse->getNavn()
			)
		);
		$zip = new zip( $zipnavn, true );
		
		foreach( $hendelse->getInnslag()->getAll() as $innslag ) {
			$qr = $qrkoder[ $innslag->getId() ];
			
			$order = $hendelse->getNummer( $innslag );
			$zip->add(
				$qr->full_path,
				$order .'. '. $innslag->getNavn().'.png'
			);
		}
		
		$zipfile = new stdClass();
		$zipfile->navn = $hendelse->getNavn();
		$zipfile->url = $zip->compress();
		
		$zipfiles[] = $zipfile;
	}
	
	$TWIG['zipfiles'] = $zipfiles;
} else {
	$TWIG['error'] = true;
}
