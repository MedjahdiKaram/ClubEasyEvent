<?php
/**
 * Club Easy Event main plugin file.
 *
 * @package ClubEasyEvent
 */

/**
 * Plugin Name: Club Easy Event
 * Plugin URI:
 * Description: Le plugin le plus simple pour gérer les événements, les équipes et les membres de votre club sportif directement dans WordPress.
 * Version: 1.0.0
 * Author:
 * Author URI:
 * License: GPLv2 or later
 * Text Domain: club-easy-event
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CEE_VERSION', '1.0.0' );
define( 'CEE_PLUGIN_FILE', __FILE__ );
define( 'CEE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CEE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once CEE_PLUGIN_DIR . 'includes/class-cee-activator.php';
require_once CEE_PLUGIN_DIR . 'includes/class-cee-deactivator.php';
require_once CEE_PLUGIN_DIR . 'includes/class-cee-i18n.php';
require_once CEE_PLUGIN_DIR . 'includes/class-cee-plugin.php';

register_activation_hook( __FILE__, array( 'CEE_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'CEE_Deactivator', 'deactivate' ) );

/**
 * Begins execution of the plugin.
 *
 * @return void
 */
function run_club_easy_event() {
	$plugin = new CEE_Plugin();
	$plugin->run();
}

run_club_easy_event();
