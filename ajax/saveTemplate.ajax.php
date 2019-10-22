<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Rapporter\Template\Samling;
use UKMNorge\Rapporter\Template\Template;
use UKMNorge\Rapporter\Template\Write;

require_once('UKM/Autoloader.php');
global $current_user;

$arrangement = new Arrangement( get_option('pl_id') );

if( $_POST['template_id'] == 'new' ) {
    $template = Write::create( $current_user->ID, (Int) get_option('pl_id'), $_POST['rapport'], $_POST['name']);
} else {
    $template = Samling::getFromId( (Int) $_POST['template_id'] );
}

parse_str( $_POST['config'], $config );
$template->setConfig($config);
$template->setBeskrivelse( $_POST['description'] );

$res = Write::save( $template );

#$templates = Samling::getFromRapport( $_POST['rapport'], $arrangement->getId() );
#UKMrapporter::addResponseData('templates', $templates->getAll());
