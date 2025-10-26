<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package ClubEasyEvent
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Les données ne sont pas supprimées automatiquement pour protéger le travail du club.
