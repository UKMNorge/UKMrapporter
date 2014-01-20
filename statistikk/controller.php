<?php

$TWIG['season']	= (int)get_option('season');

for($i=2010; $i<=$TWIG['season']; $i++) {
	$TWIG['seasons'][] = $i;
}

$pl = new monstring( get_option( 'pl_id' ) );
$monstring = new stdClass();
$monstring->name = $pl->g('pl_name');
$monstring->season = $pl->g('season');
$monstring->storrelse = 'liten';



$monstring->fylke = new StdClass();
$monstring->fylke->name = $pl->g('fylke_name');
$monstring->fylke->id = $pl->g('fylke_id');

$TWIG['kommuner'] = $pl->g('kommuner');

$TWIG['home']	= 'home';

$TWIG['monstring'] = $monstring;

ini_set('display_errors', true);

require_once('deltakere.controller.php');
require_once('sjangerfordeling.controller.php');
require_once('kjonnsfordeling.controller.php');
require_once('malgruppe.controller.php');
require_once('aldersfordeling.controller.php');