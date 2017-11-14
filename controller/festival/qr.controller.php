<?php
#error_reporting(E_ALL);
require_once('UKM/zip.class.php');	
require_once('UKM/monstring.class.php');
require_once(PLUGIN_DIR .'vendor/autoload.php');

use PHPQRCode\QRcode;
use PHPQRCode\Constants;

if( UKM_HOSTNAME == 'ukm.dev' ) {
	$protocol = 'http:';
	$path = '/var/www/qr/';
} else {
	$protocol = 'https:';
	$path = '/home/ukmno/public_subdomains/qr/';
}
$domain = $protocol . '//qr.'. UKM_HOSTNAME .'/';

define('ZIP_WRITE_PATH', $path .'zip/');

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
		$qr->file 	= $monstring->getId() .'-'. $innslag->getId();
		$qr->url	= $domain . $qr->file;
		$qr->path 	= $path . $qr->file;
		$qr->link	= $protocol . $monstring->getLink() . 'pameldte/'. $innslag->getId() .'/';
	
		$qrkoder[ $innslag->getId() ] = $qr;
		
		// PNG-utgave
		@unlink( $qr->link.'.png');
		QRcode::png(
			$qr->link, 			// STRING text string to encode
			$qr->path .'.png', 	// STRING output file name, if false outputs to browser with required headers
			'Q', 				// STRING error correction level L, M, Q or H
			300, 				// INT pixel size, multiplier for each 'virtual' pixel
			2,					// INT code margin (silent zone) in 'virtual' pixels
			false				// BOOL true: code is outputed to browser and saved to file. false: only saved to file
		);
		
		$zip->add(
			$qr->path .'.png',
			utf8_encode($innslag->getNavn().'.png')
		);
	}
	$zipfile = new stdClass();
	$zipfile->navn = 'Alfabetisk';
	$zipfile->file = $zip->compress();
	$zipfile->url = $domain .'zip/'. $zipnavn .'.zip';
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
			
			$order = $innslag->getAttr('order')+1;
			$zip->add(
				$qr->path .'.png',
				$order .'. '. $innslag->getNavn().'.png'
			);
		}
		
		$zipfile = new stdClass();
		$zipfile->navn = $hendelse->getNavn();
		$zipfile->file = $zip->compress();
		$zipfile->url = $domain .'zip/'. $zipnavn .'.zip';
		
		$zipfiles[] = $zipfile;
	}
	
	$TWIG['zipfiles'] = $zipfiles;
} else {
	$TWIG['error'] = true;
}
