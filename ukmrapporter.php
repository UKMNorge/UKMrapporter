<?php
/* 
Plugin Name: UKM Rapporter V2
Plugin URI: http://www.ukm-norge.no
Description: UKM Norge admin
Author: UKM Norge / M Mandal 
Version: 2.0 
Author URI: http://www.ukm-norge.no
*/

use UKMNorge\Wordpress\Modul;

ini_set('display_errors', true);

require_once('UKM/Autoloader.php');
spl_autoload_register(['UKMrapporter','autoload']);

class UKMrapporter extends Modul
{
    public static $action = 'rapporter';
    public static $path_plugin = null;

    public static function hook()
    {
        if (get_option('pl_id')) {
            add_action(
                'admin_menu',
                ['UKMrapporter', 'meny']
            );
        }

        if (function_exists('is_network_admin') && is_network_admin()) {
            add_action(
                'network_admin_menu',
                ['UKMrapporter', 'nettverkMeny']
            );
        }
    }

    public static function meny()
    {
        $page = add_menu_page(
            'Rapporter V2',
            'Rapporter V2',
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

        wp_enqueue_style('jquery-ui-style', self::getPluginPath() . 'UKMNorge/js/css/jquery-ui-1.7.3.custom.css');
        wp_enqueue_script('GOOGLEchart', 'https://www.google.com/jsapi');

        wp_enqueue_script('jquery');
        wp_enqueue_script('jqueryGoogleUI', '//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js');
    }

    public static function autoload($class_name)
    {
        if (strpos($class_name, 'UKMNorge\Rapporter\\') === 0) {
            $file = static::getPath().'rapporter/' . str_replace(
                ['\\', 'UKMNorge/Rapporter/'],
                ['/', ''],
                $class_name
            ) . '.php';

            if (file_exists($file)) {
                require_once( $file );
            }
        }
    }
}

UKMrapporter::init(__DIR__);
UKMrapporter::hook();