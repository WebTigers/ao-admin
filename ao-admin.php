<?php
/**
 * @package 3AO_Admin
 * @version 1.0.0
 */
/**
Plugin Name: 3AO Admin Page
Plugin URI:
Description: Lorem ipsum ...
Author: 3AO
Version: 1.0.0
Author URI: https://3ao.com
Text Domain: ao-admin-page
*/

// Make sure we don't expose any info if called directly
if ( ! function_exists( 'add_action' ) ) {
    echo '♬ I\'m just a plugin, yes I\'m only a plugin, and you shouldn\'t be trying hack into this site ... ♬';
    exit;
}

/** Quick var print functions for debugging. */
function pr ($var, $die = true) {
    header("Content-Type:text/plain");
    if ($die) { die(print_r($var,1)); } else { print_r($var,1); }
}

/** Zend var print functions for debugging. */
function zd ($var, $die = true) {
    if ($die) { die(Zend_Debug::dump($var)); } else { Zend_Debug::dump($var); }
}

define( 'AO_ADMIN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/** Some ZendFramework Includes */
$include_path = AO_ADMIN_PLUGIN_DIR . 'inc/zf1-future/library';
set_include_path(get_include_path() . PATH_SEPARATOR . $include_path);
require_once AO_ADMIN_PLUGIN_DIR . 'inc/zf1-future/library/Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

register_activation_hook( __FILE__, [ 'AOAdmin', 'plugin_activation' ] );
register_deactivation_hook( __FILE__, [ 'AOAdmin', 'plugin_deactivation' ] );

require_once AO_ADMIN_PLUGIN_DIR . 'class.ao_admin.php';
require_once AO_ADMIN_PLUGIN_DIR . 'class.ao_admin_update.php';
require_once AO_ADMIN_PLUGIN_DIR . 'class.parsedown.php';

require_once AO_ADMIN_PLUGIN_DIR . 'class.ao_form_profile.php';
require_once AO_ADMIN_PLUGIN_DIR . 'class.ao_form_size_card.php';
require_once AO_ADMIN_PLUGIN_DIR . 'class.ao_form_resume.php';
require_once AO_ADMIN_PLUGIN_DIR . 'class.ao_form_media.php';

require_once AO_ADMIN_PLUGIN_DIR . 'class.ao_model_response.php';
require_once AO_ADMIN_PLUGIN_DIR . 'class.ao_model_usermeta.php';

require_once AO_ADMIN_PLUGIN_DIR . 'class.ao_service_validation.php';
require_once AO_ADMIN_PLUGIN_DIR . 'class.ao_service_profile.php';
require_once AO_ADMIN_PLUGIN_DIR . 'class.ao_service_size_card.php';
require_once AO_ADMIN_PLUGIN_DIR . 'class.ao_service_media.php';

add_action( 'init', [ 'AOAdmin', 'init' ] );

## Test Code ##

//phpinfo();
//exit;
