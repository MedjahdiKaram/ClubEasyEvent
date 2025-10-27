<?php
/**
 * Custom post type registrations.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers custom post types for the plugin.
 */
class CEE_CPT {

	/**
	 * Register all post types.
	 *
	 * @return void
	 */
	public function register_post_types() {
		$this->register_event_cpt();
		$this->register_team_cpt();
		$this->register_player_cpt();
		$this->register_venue_cpt();
	}

	/**
	 * Register the event post type.
	 *
	 * @return void
	 */
	private function register_event_cpt() {
		$labels = array(
			'name'               => _x( 'Événements', 'Post type general name', 'club-easy-event' ),
			'singular_name'      => _x( 'Événement', 'Post type singular name', 'club-easy-event' ),
			'add_new'           => __( 'Ajouter', 'club-easy-event' ),
			'add_new_item'      => __( 'Ajouter un événement', 'club-easy-event' ),
			'edit_item'         => __( 'Modifier l\'événement', 'club-easy-event' ),
			'new_item'          => __( 'Nouvel événement', 'club-easy-event' ),
			'view_item'         => __( 'Voir l\'événement', 'club-easy-event' ),
			'search_items'      => __( 'Rechercher des événements', 'club-easy-event' ),
			'not_found'         => __( 'Aucun événement trouvé', 'club-easy-event' ),
			'not_found_in_trash'=> __( 'Aucun événement dans la corbeille', 'club-easy-event' ),
			'all_items'         => __( 'Tous les événements', 'club-easy-event' ),
		);

                $args = array(
                        'labels'             => $labels,
                        'public'             => true,
                        'has_archive'        => true,
                        'show_in_rest'       => true,
                        'supports'           => array( 'title', 'editor', 'thumbnail' ),
                        'menu_icon'          => 'dashicons-calendar-alt',
                        'menu_position'      => 5,
                        'show_in_menu'       => false,
                        'rewrite'            => array( 'slug' => 'evenements' ),
                        'capability_type'    => array( 'cee_event', 'cee_events' ),
                        'map_meta_cap'       => true,
                        'capabilities'       => $this->get_capabilities( 'cee_event', 'cee_events' ),
                );

		register_post_type( 'cee_event', $args );
	}

	/**
	 * Register the team post type.
	 *
	 * @return void
	 */
	private function register_team_cpt() {
		$labels = array(
			'name'               => _x( 'Équipes', 'Post type general name', 'club-easy-event' ),
			'singular_name'      => _x( 'Équipe', 'Post type singular name', 'club-easy-event' ),
			'add_new_item'      => __( 'Ajouter une équipe', 'club-easy-event' ),
			'edit_item'         => __( 'Modifier l\'équipe', 'club-easy-event' ),
			'new_item'          => __( 'Nouvelle équipe', 'club-easy-event' ),
			'all_items'         => __( 'Toutes les équipes', 'club-easy-event' ),
			'view_item'         => __( 'Voir l\'équipe', 'club-easy-event' ),
		);

                $args = array(
                        'labels'             => $labels,
                        'public'             => true,
                        'has_archive'        => true,
                        'show_in_rest'       => true,
                        'supports'           => array( 'title', 'editor', 'thumbnail' ),
                        'menu_icon'          => 'dashicons-groups',
                        'show_in_menu'       => false,
                        'capability_type'    => array( 'cee_team', 'cee_teams' ),
                        'map_meta_cap'       => true,
                        'capabilities'       => $this->get_capabilities( 'cee_team', 'cee_teams' ),
                );

		register_post_type( 'cee_team', $args );
	}

	/**
	 * Register the player post type.
	 *
	 * @return void
	 */
	private function register_player_cpt() {
		$labels = array(
			'name'               => _x( 'Joueurs', 'Post type general name', 'club-easy-event' ),
			'singular_name'      => _x( 'Joueur', 'Post type singular name', 'club-easy-event' ),
			'add_new_item'      => __( 'Ajouter un joueur', 'club-easy-event' ),
			'edit_item'         => __( 'Modifier le joueur', 'club-easy-event' ),
			'all_items'         => __( 'Tous les joueurs', 'club-easy-event' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'has_archive'        => false,
			'show_in_rest'       => true,
			'supports'           => array( 'title', 'thumbnail' ),
			'show_in_menu'       => 'edit.php?post_type=cee_team',
			'capability_type'    => array( 'cee_player', 'cee_players' ),
			'map_meta_cap'       => true,
			'capabilities'       => $this->get_capabilities( 'cee_player', 'cee_players', false ),
		);

		register_post_type( 'cee_player', $args );
	}

	/**
	 * Register the venue post type.
	 *
	 * @return void
	 */
	private function register_venue_cpt() {
		$labels = array(
			'name'               => _x( 'Lieux', 'Post type general name', 'club-easy-event' ),
			'singular_name'      => _x( 'Lieu', 'Post type singular name', 'club-easy-event' ),
			'add_new_item'      => __( 'Ajouter un lieu', 'club-easy-event' ),
			'edit_item'         => __( 'Modifier le lieu', 'club-easy-event' ),
			'all_items'         => __( 'Tous les lieux', 'club-easy-event' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'supports'           => array( 'title' ),
			'show_in_menu'       => 'edit.php?post_type=cee_event',
			'capability_type'    => array( 'cee_venue', 'cee_venues' ),
			'map_meta_cap'       => true,
			'capabilities'       => $this->get_capabilities( 'cee_venue', 'cee_venues', false ),
		);

		register_post_type( 'cee_venue', $args );
	}

	/**
	 * Build capability mapping for custom post types.
	 *
	 * @param string  $singular Singular capability base.
	 * @param string  $plural   Plural capability base.
	 * @param boolean $include_publish Include publish caps.
	 *
	 * @return array
	 */
	private function get_capabilities( $singular, $plural, $include_publish = true ) {
		$caps = array(
			'edit_post'              => "edit_{$singular}",
			'read_post'              => "read_{$singular}",
			'delete_post'            => "delete_{$singular}",
			'edit_posts'             => "edit_{$plural}",
			'edit_others_posts'      => "edit_others_{$plural}",
			'delete_posts'           => "delete_{$plural}",
			'delete_others_posts'    => "delete_others_{$plural}",
			'read_private_posts'     => "read_private_{$plural}",
			'edit_published_posts'   => "edit_published_{$plural}",
			'delete_published_posts' => "delete_published_{$plural}",
		);

		if ( $include_publish ) {
			$caps['publish_posts'] = "publish_{$plural}";
		}

		return $caps;
	}
}
