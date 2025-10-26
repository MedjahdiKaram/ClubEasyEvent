<?php
/**
 * Deactivation handler.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once CEE_PLUGIN_DIR . 'includes/class-cee-cron.php';

/**
 * Handles plugin deactivation tasks.
 */
class CEE_Deactivator {

	/**
	 * Run deactivation logic.
	 *
	 * @return void
	 */
	public static function deactivate() {
		CEE_Cron::deactivate_schedule();
		flush_rewrite_rules();
	}
}
