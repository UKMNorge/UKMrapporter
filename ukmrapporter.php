<?php
/* 
Plugin Name: UKM Rapporter
Plugin URI: http://www.ukm-norge.no
Description: UKM Norge admin
Author: UKM Norge / M Mandal 
Version: 2.0 
Author URI: http://www.ukm-norge.no
*/

use UKMNorge\Wordpress\Modul;
use UKMNorge\Arrangement\Arrangement;

// ini_set('display_errors', true);
require_once('UKM/Autoloader.php');
spl_autoload_register(['UKMrapporter','autoload']);

class UKMrapporter extends Modul
{
    public static $action = 'rapporter';
    public static $path_plugin = null;

    public static function hook()
    {

        add_action(
            'wp_ajax_UKMrapporter_ajax',
            ['UKMrapporter', 'ajax']
        );

        if (get_option('pl_id')) {
            add_action(
                'admin_menu',
                ['UKMrapporter', 'meny']
            );
        }
        add_action(
            'user_admin_menu',
            ['UKMrapporter', 'userMeny']
        );

        if (function_exists('is_network_admin') && is_network_admin()) {
            add_action(
                'network_admin_menu',
                ['UKMrapporter', 'nettverkMeny']
            );
        }
    }

    /**
     * Legg til menyelement
     *
     * @return void
     */
    public static function meny()
    {
        $page = add_menu_page(
            'Rapporter',
            'Rapporter',
            'ukm_rapporter',
            'UKMrapporter',
            ['UKMrapporter', 'renderAdmin'],
            'dashicons-analytics',
            81
        );

        add_action(
            'admin_print_styles-' . $page,
            ['UKMrapporter', 'scripts_and_styles']
        );
    }
    
    /**
     * Legg til menyelement i user-admin
     */
    public static function userMeny()
    {
        add_action(
            'admin_print_styles-'. add_menu_page(
                'Rapporter',
                'Rapporter',
                'subscriber',
                'UKMrapporter',
                ['UKMrapporter', 'renderAdmin'],
                'dashicons-analytics',
                81
            ),
            ['UKMrapporter', 'scripts_and_styles']
        );
    }

    public static function renderAdmin()
    {
        static::addViewData('aktivt_senter', is_user_admin() ? 'bruker' : (is_network_admin() ? 'network':'arrangement'));
        
        if(get_option('pl_id')) {
            static::addViewData('arrangement', new Arrangement(get_option('pl_id')));
        }
        return parent::renderAdmin();
    }

    public static function nettverkMeny()
    {
        $page = add_menu_page(
            'Rapporter',
            'Rapporter',
            'superadmin',
            'UKMrapporter',
            ['UKMrapporter', 'renderAdmin'],
            'dashicons-analytics'
        );
        add_action(
            'admin_print_styles-' . $page,
            ['UKMrapporter', 'scripts_and_styles']
        );
    }

    public static function scripts_and_styles()
    {
        wp_enqueue_script('WPbootstrap3_js');
        wp_enqueue_style('WPbootstrap3_css');
        wp_enqueue_script('TwigJS');

        wp_enqueue_style('UKMrapporter_css', static::getPluginUrl() . 'ukmrapporter.css');
        wp_enqueue_script('UKMrapporter_js', static::getPluginUrl() . 'ukmrapporter.js');

        wp_enqueue_style('jquery-ui-style');
        wp_enqueue_script('GOOGLEchart', 'https://www.google.com/jsapi');

        wp_enqueue_script('jquery');
        wp_enqueue_script('jqueryGoogleUI', '//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js');
    	wp_enqueue_style('UKMSMSVueMIDIcons', 'https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css');


        wp_enqueue_script('jsPDF', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js');
        wp_enqueue_script('jsPDFTable', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.14/jspdf.plugin.autotable.min.js');


        wp_enqueue_style('UKMrapporterVueStyle', plugin_dir_url(__FILE__) . '/client/dist/assets/build.css');
        wp_enqueue_script('UKMrapporterVueJs', plugin_dir_url(__FILE__) . '/client/dist/assets/build.js','','',true);

        wp_enqueue_style('UKMvideoArrSysStyle', '//assets.' . UKM_HOSTNAME . '//css/arr-sys.css');
    }

    public static function autoload($class_name)
    {
        if (strpos($class_name, 'UKMNorge\Rapporter\\') === 0) {
            if( strpos( $class_name, 'UKMNorge\Rapporter\Template\\') === 0 || strpos( $class_name, 'UKMNorge\Rapporter\Framework\\') === 0) {
                $path = static::getPath().'class/';
            } else {
                $path = static::getPath().'rapporter/';
            }
            $file = $path . str_replace(
                ['\\', 'UKMNorge/Rapporter/'],
                ['/', ''],
                $class_name
            ) . '.php';

            if (file_exists($file)) {
                require_once( $file );
            }
        }
    }

    public static function getAktivRapport($rapport=false) {
        if( !$rapport ) {
            $rapport = $_GET['rapport'];
        }
        $class = 'UKMNorge\Rapporter\\' . basename($rapport);
        return new $class();
    }
}

UKMrapporter::init(__DIR__);
UKMrapporter::hook();
