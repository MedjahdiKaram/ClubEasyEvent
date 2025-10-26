<?php
/**
 * Activation handler.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once CEE_PLUGIN_DIR . 'includes/class-cee-cpt.php';
require_once CEE_PLUGIN_DIR . 'includes/class-cee-taxonomies.php';
require_once CEE_PLUGIN_DIR . 'includes/class-cee-roles.php';
require_once CEE_PLUGIN_DIR . 'includes/class-cee-cron.php';

/**
 * Handles plugin activation tasks.
 */
class CEE_Activator {

	/**
	 * Run activation logic.
	 *
	 * @return void
	 */
	public static function activate() {
		self::register_content_types();
		CEE_Roles::add_roles();
		CEE_Cron::activate_schedule();
		self::add_default_options();
		flush_rewrite_rules();
	}

	/**
	 * Register CPTs and taxonomies during activation.
	 *
	 * @return void
	 */
	private static function register_content_types() {
		$cpt        = new CEE_CPT();
		$taxonomies = new CEE_Taxonomies();

		$cpt->register_post_types();
		$taxonomies->register_taxonomies();
	}

	/**
	 * Add default plugin options.
	 *
	 * @return void
	 */
	private static function add_default_options() {
		$defaults = array(
			'email_template' => __( 'Bonjour {user_name},\n\nVotre prochain événement est {event_name} le {event_date} à {event_time}. Retrouvez toutes les informations ici : {event_link}.\n\nSportivement,', 'club-easy-event' ),
			'primary_color'  => '#0d6efd',
		);

		if ( ! get_option( 'cee_settings', false ) ) {
			add_option( 'cee_settings', $defaults, '', false );
		}
	}
}
