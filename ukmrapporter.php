<?php  
/* 
Plugin Name: UKM Rapporter
Plugin URI: http://www.ukm-norge.no
Description: UKM Norge admin
Author: UKM Norge / M Mandal 
Version: 1.0 
Author URI: http://www.ukm-norge.no
*/
if(is_admin()) {
	global $blog_id;
	if($blog_id != 1)
		add_action('UKM_admin_menu', 'UKMrapport_menu');

	require_once('UKM/inc/toolkit.inc.php');
	require_once('UKM/inc/phaseout.ico.inc.php');
	require_once('rapporter.ajax.php');
	
	add_action('wp_ajax_UKMrapport_ajax', 'UKMrapport_ajax');
	
	add_action('wp_ajax_UKMrapport_countPrint', 'UKMrapport_countPrint');
}

function UKMrapport_countPrint(){
	$qry = new SQLins('log_rapporter_format');
	$qry->add('f_type', $_POST['log']);
	$qry->add('f_rapport', $_POST['get']);
	$qry->add('f_pl_id', get_option('pl_id'));
	$qry->add('f_season', $_POST['season']);
	$qry->run();
}

## CREATE A MENU
function UKMrapport_menu() {
	UKM_add_menu_page('monstring','Rapporter', 'Rapporter', 'editor', 'UKMrapport_admin', 'UKMrapport_admin', 'http://ico.ukm.no/graph-menu.png',15);    
	UKM_add_scripts_and_styles( 'UKMrapport_admin', 'UKMrapport_scriptsandstyles' );
	if(isset($_GET['stat'])) {
		UKM_add_scripts_and_styles( 'UKMrapport_admin', 'UKMrapport_statistikk_scripts_and_styles' );
	}
}

function UKMrapport_scriptsandstyles() {
	wp_enqueue_style( 'jquery-ui-style', WP_PLUGIN_URL .'/UKMNorge/js/css/jquery-ui-1.7.3.custom.css');
	wp_enqueue_style( 'UKMrapporter_style', WP_PLUGIN_URL .'/UKMrapporter/rapporter.style.css');
#	wp_enqueue_style( 'UKMrapport_program', WP_PLUGIN_URL .'/UKMrapport/program.style.css');

	wp_enqueue_script('GOOGLEchart', 'https://www.google.com/jsapi');

	wp_enqueue_script('jquery');
	wp_enqueue_script('jqueryGoogleUI', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js');
/*
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('jquery-ui-effects-core');
	wp_enqueue_script('jquery-ui-effects', '/wp-content/plugins/project_manager/scripts/ui.effects.js');
*/

	wp_enqueue_script('UKMprintarea', WP_PLUGIN_URL . '/UKMrapporter/printarea.script.js' );
	wp_enqueue_script('UKMrapport_script', WP_PLUGIN_URL . '/UKMrapporter/rapport.script.js' );
	
}

## SHOW STATS OF PLACES
function UKMrapport_admin() {
	if(isset($_GET['stat'])) {
		$VIEW = $_GET['stat'];
		$TWIG = array();
		require_once('statistikk/controller.php');

		echo TWIG( $VIEW.'.twig.html', $TWIG, dirname(__FILE__), true);
		echo HANDLEBARS( dirname(__FILE__) );

	} elseif(isset($_GET['rapport'])&&$_GET['rapport']!=='undefined'&&isset($_GET['kat'])) {
		require_once('class.rapport.php');
		require_once('rapport/'.$_GET['kat'].'/'.$_GET['rapport'].'.report.php');
		require_once('gui.rapport.php');
	} else {
		require_once('gui.rapporter.php');
	}
}

function UKMrapport_statistikk_scripts_and_styles(){
	wp_enqueue_script('handlebars_js');
	wp_enqueue_script('WPbootstrap3_js');
	wp_enqueue_style('WPbootstrap3_css');
	wp_enqueue_style('UKMresources_tabs');

	wp_enqueue_script('UKMrapport_statistikk_script', WP_PLUGIN_URL . '/UKMrapporter/statistikk.rapporter.js' );
	wp_enqueue_style('UKMrapport_statistikk_style', WP_PLUGIN_URL . '/UKMrapporter/statistikk.rapporter.css' );

}
?>