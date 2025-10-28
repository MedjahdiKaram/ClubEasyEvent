<?php
/**
 * Role and capability helpers.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages custom roles for the plugin.
 */
class CEE_Roles {

	/**
	 * Add plugin roles on activation.
	 *
	 * @return void
	 */
	public static function add_roles() {
		add_role( 'team_manager', __( 'Manager d’équipe', 'club-easy-event' ), self::get_role_capabilities() );
		( new self() )->register_caps();
	}

	/**
	 * Ensure capabilities are assigned to roles.
	 *
	 * @return void
	 */
	public function register_caps() {
		$roles = array( 'administrator', 'editor', 'team_manager' );
		foreach ( $roles as $role_name ) {
			$role = get_role( $role_name );
			if ( ! $role ) {
				continue;
			}
			foreach ( self::get_capability_keys() as $cap ) {
				$role->add_cap( $cap );
			}
		}
	}

	/**
	 * Get base capabilities for the custom role.
	 *
	 * @return array
	 */
	private static function get_role_capabilities() {
		$caps = array_fill_keys( self::get_capability_keys(), true );
		$caps['read']                 = true;
		$caps['read_private_posts']   = true;
		$caps['edit_posts']           = true;
		$caps['edit_others_posts']    = true;
		$caps['publish_posts']        = true;
		$caps['delete_posts']         = true;
		$caps['delete_others_posts']  = true;
		$caps['edit_private_posts']   = true;
		$caps['edit_published_posts'] = true;
		$caps['delete_private_posts'] = true;
		$caps['delete_published_posts'] = true;
		$caps['upload_files']         = true;

		return $caps;
	}

	/**
	 * All custom capability keys.
	 *
	 * @return array
	 */
	private static function get_capability_keys() {
                return array(
                        'edit_cee_event',
                        'edit_cee_events',
                        'edit_others_cee_events',
                        'publish_cee_events',
                        'delete_cee_events',
                        'delete_others_cee_events',
                        'read_cee_event',
                        'cee_approve_content',
                        'cee_mark_pending',
                        'cee_reject_content',
                        'edit_cee_team',
                        'edit_cee_teams',
                        'edit_others_cee_teams',
                        'publish_cee_teams',
                        'delete_cee_teams',
			'delete_others_cee_teams',
			'read_cee_team',
			'edit_cee_player',
			'edit_cee_players',
			'delete_cee_players',
			'read_cee_player',
			'edit_cee_venue',
			'edit_cee_venues',
			'delete_cee_venues',
			'read_cee_venue',
		);
	}
}
