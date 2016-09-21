<?php
require_once('UKM/fylker.class.php');
require_once('UKM/monstringer.class.php');

$fylke = fylker::getById( $_GET['fylke'] );
$fylke->setAttr('sesong', $_GET['sesong'] );
$monstringCollection = new monstringer_v2( $fylke->getAttr('sesong') );

$monstringer = $monstringCollection->utenGjester( $monstringCollection->getAllByFylke( $fylke ) );

foreach( $monstringer as $array_pos => $monstring ) {
	if( !$monstring->erRegistrert() ) {
		unset( $monstringer[ $array_pos ] );
	}
}
$fylke->setAttr('monstringer', $monstringer);

$TWIG['fylke'] = $fylke;