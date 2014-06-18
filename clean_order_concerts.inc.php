<?php
require_once('UKM/forestilling.class.php');
$m = new monstring( get_option('pl_id') );

$hendelser = $m->forestillinger('c_start', false);

foreach( $hendelser as $hen ) {
	$h = new forestilling( $hen['c_id'] );
	$h->reCount();
}