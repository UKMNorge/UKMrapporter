<?php
require_once('UKM/forestilling.class.php');
$m = new monstring( get_option('pl_id') );

$hendelser = $m->forestillinger();

foreach( $hendelser as $hen ) {
	$h = new forestilling( $hen['c_id'] );
	$h->reCount();
}