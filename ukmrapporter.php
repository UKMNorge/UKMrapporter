<?php  
/* 
Plugin Name: UKM Rapporter
Plugin URI: http://www.ukm-norge.no
Description: UKM Norge admin
Author: UKM Norge / M Mandal 
Version: 1.0 
Author URI: http://www.ukm-norge.no
*/
ini_set('display_errors', true);
define('PLUGIN_DIR_UKMRAPPORTER', dirname( __FILE__ ).'/' );

if(is_admin()) {

	if( in_array( get_option('site_type'), array('kommune','fylke','land')) ) {
		add_action('admin_menu', 'UKMrapport_menu');
		add_action('UKMWPDASH_shortcuts', 'UKMMrapport_dash_shortcut', 50);
	}
	require_once('UKM/inc/toolkit.inc.php');
	require_once('rapporter.ajax.php');
	
	add_action('wp_ajax_UKMrapport_ajax', 'UKMrapport_ajax');
	
	add_action('wp_ajax_UKMrapport_countPrint', 'UKMrapport_countPrint');
}
add_action('network_admin_menu', 'UKMrapport_network_menu');


function UKMrapport_network_menu() {
	$page = add_menu_page(
		'Rapporter',
		'Rapporter',
		'superadmin',
		'UKMrapport_admin',
		'UKMrapport_admin',
		'dashicons-analytics',#'//ico.ukm.no/graph-menu.png',
		2101
	);
	add_action( 'admin_print_styles-' . $page, 'UKMrapport_scriptsandstyles' );
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
	$page = add_menu_page(
		'Rapporter',
		'Rapporter',
		'ukm_rapporter',
		'UKMrapport_admin',
		'UKMrapport_admin',
		'dashicons-analytics',#'//ico.ukm.no/graph-menu.png',
		80
	);

	add_action(
		'admin_print_styles-' . $page,
		'UKMrapport_scriptsandstyles'
	);

	if(isset($_GET['stat'])||isset($_GET['fylkestimeplan'])||isset($_GET['festival'])) {
		add_action(
			'admin_print_styles-' . $page,
			'UKMrapport_statistikk_scripts_and_styles'
		);
	}
}
function UKMMrapport_dash_shortcut( $shortcuts ) {	
	$shortcut = new stdClass();
	$shortcut->url = 'admin.php?page=UKMrapport_admin';
	$shortcut->title = 'Rapporter';
	$shortcut->icon = '//ico.ukm.no/graph-menu.png';
	$shortcuts[] = $shortcut;
	
	return $shortcuts;
}


function UKMrapport_scriptsandstyles() {
	wp_enqueue_script('WPbootstrap3_js');
	wp_enqueue_style('WPbootstrap3_css');

	wp_enqueue_style( 'jquery-ui-style', WP_PLUGIN_URL .'/UKMNorge/js/css/jquery-ui-1.7.3.custom.css');
	wp_enqueue_style( 'UKMrapporter_style', WP_PLUGIN_URL .'/UKMrapporter/rapporter.style.css');

	wp_enqueue_script('GOOGLEchart', 'https://www.google.com/jsapi');

	wp_enqueue_script('jquery');
	wp_enqueue_script('jqueryGoogleUI', '//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js');

	wp_enqueue_script('UKMprintarea', WP_PLUGIN_URL . '/UKMrapporter/printarea.script.js' );
	wp_enqueue_script('UKMrapport_script', WP_PLUGIN_URL . '/UKMrapporter/rapport.script.js' );
	
}

## SHOW STATS OF PLACES
function UKMrapport_admin() {
	define('PLUGIN_DIR', dirname( __FILE__ ).'/' );

	$TWIG = array();
	if(isset($_GET['stat'])) {
		$VIEW = $_GET['stat'];
		require_once('statistikk/controller.php');

		echo TWIG( $VIEW.'.twig.html', $TWIG, dirname(__FILE__), true);
		echo HANDLEBARS( dirname(__FILE__) );
	} elseif( isset( $_GET['festival'] )) {
		$VIEW = $_GET['festival'];
		require_once('controller/festival/'. $VIEW .'.controller.php');
		echo TWIG('festival/'. $VIEW .'.html.twig', $TWIG, dirname(__FILE__), true);		
	} elseif( isset( $_GET['fylkestimeplan'] )) {
		$VIEW = 'fylkestimeplan';
		require_once('fylkestimeplan/controller.php');
		echo TWIG( 'fylkestimeplan/generate.twig.html', $TWIG, dirname(__FILE__), true);
	} elseif(isset($_GET['rapport'])&&$_GET['rapport']!=='undefined'&&isset($_GET['kat'])) {
		require_once('class.rapport.php');
		require_once('rapport/'.$_GET['kat'].'/'.$_GET['rapport'].'.report.php');
		require_once('gui.rapport.php');
	} elseif( isset( $_GET['network'] ) ) {
		$VIEW = $_GET['network'];
		require_once('controller/network/'. $_GET['network'] .'.controller.php');
		echo TWIG( 'network/'. $VIEW . '.html.twig', $TWIG, dirname( __FILE__ ), true );
	} elseif( is_network_admin() ) {
		require_once('controller/dashboard_network.controller.php');
		echo TWIG('dashboard.html.twig', $TWIG, dirname(__FILE__), true);
	} else {
		require_once('clean_order_concerts.inc.php');
		require_once('controller/dashboard.controller.php');
		echo TWIG('dashboard.html.twig', $TWIG, dirname(__FILE__), true);
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