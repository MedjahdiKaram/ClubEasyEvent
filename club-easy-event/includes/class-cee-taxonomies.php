<?php
/**
 * Custom taxonomy registrations.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers plugin taxonomies.
 */
class CEE_Taxonomies {

	/**
	 * Register all taxonomies.
	 *
	 * @return void
	 */
	public function register_taxonomies() {
		$this->register_season_taxonomy();
		$this->register_league_taxonomy();
	}

	/**
	 * Register the season taxonomy.
	 *
	 * @return void
	 */
	private function register_season_taxonomy() {
		$labels = array(
			'name'          => _x( 'Saisons', 'Taxonomy general name', 'club-easy-event' ),
			'singular_name' => _x( 'Saison', 'Taxonomy singular name', 'club-easy-event' ),
			'search_items'  => __( 'Rechercher des saisons', 'club-easy-event' ),
			'all_items'     => __( 'Toutes les saisons', 'club-easy-event' ),
			'edit_item'     => __( 'Modifier la saison', 'club-easy-event' ),
			'update_item'   => __( 'Mettre à jour la saison', 'club-easy-event' ),
			'add_new_item'  => __( 'Ajouter une saison', 'club-easy-event' ),
			'menu_name'     => __( 'Saisons', 'club-easy-event' ),
		);

		$args = array(
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'labels'            => $labels,
		);

		register_taxonomy( 'cee_season', array( 'cee_event', 'cee_team' ), $args );
	}

	/**
	 * Register the league taxonomy.
	 *
	 * @return void
	 */
	private function register_league_taxonomy() {
		$labels = array(
			'name'          => _x( 'Ligues', 'Taxonomy general name', 'club-easy-event' ),
			'singular_name' => _x( 'Ligue', 'Taxonomy singular name', 'club-easy-event' ),
			'all_items'     => __( 'Toutes les ligues', 'club-easy-event' ),
			'edit_item'     => __( 'Modifier la ligue', 'club-easy-event' ),
			'add_new_item'  => __( 'Ajouter une ligue', 'club-easy-event' ),
			'menu_name'     => __( 'Ligues', 'club-easy-event' ),
		);

		$args = array(
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'labels'            => $labels,
		);

		register_taxonomy( 'cee_league', array( 'cee_event', 'cee_team' ), $args );
	}
}
