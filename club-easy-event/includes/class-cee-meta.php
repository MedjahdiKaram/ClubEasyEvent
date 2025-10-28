<?php
/**
 * Meta boxes and term meta management.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles registration and saving of meta boxes and taxonomy metadata.
 */
class CEE_Meta {

        /**
         * Assignment manager.
         *
         * @var CEE_Assignment|null
         */
        protected $assignment;

        /**
         * Constructor.
         *
         * @param CEE_Assignment|null $assignment Assignment manager.
         */
        public function __construct( CEE_Assignment $assignment = null ) {
                $this->assignment = $assignment;
        }

        /**
         * Register additional admin hooks.
         *
         * @param CEE_Loader $loader Loader instance.
         *
         * @return void
         */
        public function register_admin_hooks( CEE_Loader $loader ) {
                $loader->add_action( 'admin_notices', $this, 'render_event_validation_notice' );
        }

	/**
	 * Register meta boxes for post types.
	 *
	 * @return void
	 */
	public function register_meta_boxes() {
		add_meta_box( 'cee-event-details', __( 'Détails de l\'événement', 'club-easy-event' ), array( $this, 'render_event_meta_box' ), 'cee_event', 'normal', 'default' );
		add_meta_box( 'cee-team-players', __( 'Joueurs de l\'équipe', 'club-easy-event' ), array( $this, 'render_team_meta_box' ), 'cee_team', 'normal', 'default' );
		add_meta_box( 'cee-player-details', __( 'Détails du joueur', 'club-easy-event' ), array( $this, 'render_player_meta_box' ), 'cee_player', 'normal', 'default' );
		add_meta_box( 'cee-venue-details', __( 'Détails du lieu', 'club-easy-event' ), array( $this, 'render_venue_meta_box' ), 'cee_venue', 'normal', 'default' );
	}

	/**
	 * Render event meta box.
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return void
	 */
	public function render_event_meta_box( $post ) {
		$event_date      = get_post_meta( $post->ID, '_cee_event_date', true );
		$event_time      = get_post_meta( $post->ID, '_cee_event_time', true );
		$event_type      = get_post_meta( $post->ID, '_cee_event_type', true );
		$home_team_id    = absint( get_post_meta( $post->ID, '_cee_home_team_id', true ) );
		$away_team_value = get_post_meta( $post->ID, '_cee_away_team_id', true );
		$venue_id        = absint( get_post_meta( $post->ID, '_cee_venue_id', true ) );
		$home_score      = get_post_meta( $post->ID, '_cee_home_score', true );
		$away_score      = get_post_meta( $post->ID, '_cee_away_score', true );
                $teams           = $this->get_posts_for_select( 'cee_team' );
                $venues          = $this->get_posts_for_select( 'cee_venue' );
                $event_types     = self::get_event_types();
                $event_type_key  = self::get_event_type_key( $event_type );
                $validation_messages = $this->get_event_validation_messages( $post->ID );

                include CEE_PLUGIN_DIR . 'admin/views/meta-event-advanced.php';
	}

	/**
	 * Render team meta box.
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return void
	 */
        public function render_team_meta_box( $post ) {
                $player_ids = array_map( 'absint', (array) get_post_meta( $post->ID, '_cee_team_players', true ) );
                if ( $this->assignment ) {
                        $players = $this->assignment->get_players_for_assignment();
                } else {
                        $players = $this->get_posts_for_select( 'cee_player' );
                }

                include CEE_PLUGIN_DIR . 'admin/views/meta-team.php';
        }

	/**
	 * Render player meta box.
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return void
	 */
        public function render_player_meta_box( $post ) {
                $number    = get_post_meta( $post->ID, '_cee_player_number', true );
                $position  = get_post_meta( $post->ID, '_cee_player_position', true );
                $user_id   = absint( get_post_meta( $post->ID, '_cee_player_user_id', true ) );
                $team_ids  = array_map( 'absint', (array) get_post_meta( $post->ID, '_cee_player_teams', true ) );
                $users     = $this->get_users_for_select();
                if ( $this->assignment ) {
                        $teams = $this->assignment->get_teams_for_assignment();
                } else {
                        $teams = $this->get_posts_for_select( 'cee_team' );
                }

                include CEE_PLUGIN_DIR . 'admin/views/meta-player.php';
        }

	/**
	 * Render venue meta box.
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return void
	 */
	public function render_venue_meta_box( $post ) {
		$address  = get_post_meta( $post->ID, '_cee_venue_address', true );
		$map_link = get_post_meta( $post->ID, '_cee_venue_map_link', true );

		include CEE_PLUGIN_DIR . 'admin/views/meta-venue.php';
	}

	/**
	 * Save event meta data.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 *
	 * @return void
	 */
        public function save_event_meta( $post_id, $post ) {
		if ( 'cee_event' !== $post->post_type ) {
			return;
		}

		if ( ! $this->can_save_meta( $post_id, 'cee_event_meta_nonce', 'cee_event_meta' ) ) {
			return;
		}

                $event_date = isset( $_POST['cee_event_date'] ) ? wp_unslash( $_POST['cee_event_date'] ) : '';
                $event_date = self::sanitize_event_date( $event_date );
                update_post_meta( $post_id, '_cee_event_date', $event_date );

                $event_time = isset( $_POST['cee_event_time'] ) ? wp_unslash( $_POST['cee_event_time'] ) : '';
                $event_time = self::sanitize_event_time( $event_time );
                update_post_meta( $post_id, '_cee_event_time', $event_time );

		$event_type_input = isset( $_POST['cee_event_type'] ) ? sanitize_text_field( wp_unslash( $_POST['cee_event_type'] ) ) : '';
		$event_type_key   = self::get_event_type_key( $event_type_input );
		update_post_meta( $post_id, '_cee_event_type', $event_type_key );

		$home_team_id = isset( $_POST['cee_home_team_id'] ) ? absint( $_POST['cee_home_team_id'] ) : 0;
		update_post_meta( $post_id, '_cee_home_team_id', $home_team_id );

		$away_team_select = isset( $_POST['cee_away_team_id_select'] ) ? wp_unslash( $_POST['cee_away_team_id_select'] ) : '';
		$away_team_text   = isset( $_POST['cee_away_team_id_text'] ) ? wp_unslash( $_POST['cee_away_team_id_text'] ) : '';
		$away_team_raw    = $away_team_select ? $away_team_select : $away_team_text;
		if ( is_numeric( $away_team_raw ) ) {
			$away_team_value = absint( $away_team_raw );
		} else {
			$away_team_value = sanitize_text_field( $away_team_raw );
		}
		update_post_meta( $post_id, '_cee_away_team_id', $away_team_value );

		$venue_id = isset( $_POST['cee_venue_id'] ) ? absint( $_POST['cee_venue_id'] ) : 0;
		update_post_meta( $post_id, '_cee_venue_id', $venue_id );

		$home_score = isset( $_POST['cee_home_score'] ) ? max( 0, absint( $_POST['cee_home_score'] ) ) : '';
		update_post_meta( $post_id, '_cee_home_score', '' === $home_score ? '' : $home_score );

                $away_score = isset( $_POST['cee_away_score'] ) ? max( 0, absint( $_POST['cee_away_score'] ) ) : '';
                update_post_meta( $post_id, '_cee_away_score', '' === $away_score ? '' : $away_score );

                $validation_data = array(
                        'event_type'         => $event_type_key,
                        'home_team_id'       => $home_team_id,
                        'venue_id'           => $venue_id,
                        'away_team_select'   => isset( $_POST['cee_away_team_id_select'] ) ? sanitize_text_field( wp_unslash( $_POST['cee_away_team_id_select'] ) ) : '',
                        'away_team_text'     => isset( $_POST['cee_away_team_id_text'] ) ? sanitize_text_field( wp_unslash( $_POST['cee_away_team_id_text'] ) ) : '',
                );

                $this->handle_event_validation_feedback( $post_id, $validation_data );
        }

        /**
         * Handle validation feedback and persist notices.
         *
         * @param int   $post_id Post ID.
         * @param array $data    Data to validate.
         *
         * @return void
         */
        protected function handle_event_validation_feedback( $post_id, array $data ) {
                $messages = $this->validate_event_data( $post_id, $data );

                $transient_key = $this->get_event_validation_transient_key( $post_id );
                if ( empty( $messages['fields'] ) && empty( $messages['global'] ) ) {
                        delete_transient( $transient_key );
                        return;
                }

                set_transient( $transient_key, $messages, 5 * MINUTE_IN_SECONDS );

                if ( ! empty( $messages['global'] ) ) {
                        $notice_key = $this->get_event_validation_notice_key();
                        set_transient( $notice_key, $messages['global'], MINUTE_IN_SECONDS );
                }
        }

        /**
         * Validate event data according to business rules.
         *
         * @param int   $post_id Post ID.
         * @param array $data    Data array.
         *
         * @return array
         */
        protected function validate_event_data( $post_id, array $data ) {
                $messages = array(
                        'fields' => array(),
                        'global' => array(),
                );

                $event_type = isset( $data['event_type'] ) ? $data['event_type'] : '';
                $home_team  = isset( $data['home_team_id'] ) ? absint( $data['home_team_id'] ) : 0;
                $venue_id   = isset( $data['venue_id'] ) ? absint( $data['venue_id'] ) : 0;
                $away_select = isset( $data['away_team_select'] ) ? $data['away_team_select'] : '';
                $away_text   = isset( $data['away_team_text'] ) ? $data['away_team_text'] : '';

                if ( in_array( $event_type, array( 'match', 'training' ), true ) && ! $home_team ) {
                        $messages['fields']['home_team'][] = array(
                                'type' => 'error',
                                'text' => __( 'Sélectionnez une équipe à domicile pour ce type d’événement.', 'club-easy-event' ),
                        );
                        $messages['global'][] = __( 'L’équipe à domicile est obligatoire pour ce type d’événement.', 'club-easy-event' );
                }

                if ( ! $venue_id ) {
                        $messages['fields']['venue'][] = array(
                                'type' => 'warning',
                                'text' => __( 'Aucun lieu n’est défini. Pensez à le préciser pour faciliter l’organisation.', 'club-easy-event' ),
                        );
                        $messages['global'][] = __( 'L’emplacement de l’événement est recommandé pour informer les participants.', 'club-easy-event' );
                }

                if ( $away_select && $away_text ) {
                        $messages['fields']['away_team'][] = array(
                                'type' => 'warning',
                                'text' => __( 'Choisissez soit une équipe interne, soit un libellé externe pour l’adversaire.', 'club-easy-event' ),
                        );
                        $messages['global'][] = __( 'Merci de choisir une seule source pour l’équipe adverse (interne ou externe).', 'club-easy-event' );
                }

                /**
                 * Filter event validation messages.
                 *
                 * @param array $messages Messages array.
                 * @param int   $post_id  Event ID.
                 * @param array $data     Raw data.
                 */
                return apply_filters( 'cee_event_validation_rules', $messages, $post_id, $data );
        }

        /**
         * Retrieve validation messages for rendering in the UI.
         *
         * @param int $post_id Post ID.
         *
         * @return array
         */
        public function get_event_validation_messages( $post_id ) {
                $transient_key = $this->get_event_validation_transient_key( $post_id );
                $messages      = get_transient( $transient_key );
                if ( $messages ) {
                        delete_transient( $transient_key );
                }

                if ( ! is_array( $messages ) ) {
                        $messages = array(
                                'fields' => array(),
                                'global' => array(),
                        );
                }

                return $messages;
        }

        /**
         * Render validation notice after saving.
         *
         * @return void
         */
        public function render_event_validation_notice() {
                if ( ! is_admin() ) {
                        return;
                }

                $notice_key = $this->get_event_validation_notice_key();
                $messages   = get_transient( $notice_key );
                if ( ! $messages ) {
                        return;
                }

                delete_transient( $notice_key );

                if ( empty( $messages ) || ! is_array( $messages ) ) {
                        return;
                }

                if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
                        $post_id = absint( $_GET['post'] );
                        if ( $post_id && 'cee_event' !== get_post_type( $post_id ) ) {
                                return;
                        }
                }

                echo '<div class="notice notice-warning"><p>';
                foreach ( $messages as $message ) {
                        echo esc_html( $message ) . '<br />';
                }
                echo '</p></div>';
        }

        /**
         * Get transient key for field-level validation messages.
         *
         * @param int $post_id Post ID.
         *
         * @return string
         */
        protected function get_event_validation_transient_key( $post_id ) {
                $user = get_current_user_id();
                return 'cee_event_validation_' . $user . '_' . absint( $post_id );
        }

        /**
         * Get transient key for global notices.
         *
         * @return string
         */
        protected function get_event_validation_notice_key() {
                return 'cee_event_validation_notice_' . get_current_user_id();
        }

	/**
	 * Save team meta.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 *
	 * @return void
	 */
	public function save_team_meta( $post_id, $post ) {
		if ( 'cee_team' !== $post->post_type ) {
			return;
		}

		if ( ! $this->can_save_meta( $post_id, 'cee_team_meta_nonce', 'cee_team_meta' ) ) {
			return;
		}

		$players = array();
		if ( isset( $_POST['cee_team_players'] ) && is_array( $_POST['cee_team_players'] ) ) {
			foreach ( $_POST['cee_team_players'] as $player_id ) {
				$players[] = absint( $player_id );
			}
		}

                $players = array_values( array_unique( array_filter( $players ) ) );
                if ( $this->assignment ) {
                        $this->assignment->sync_team_players( $post_id, $players );
                } else {
                        update_post_meta( $post_id, '_cee_team_players', $players );
                }
        }

	/**
	 * Save player meta.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 *
	 * @return void
	 */
	public function save_player_meta( $post_id, $post ) {
		if ( 'cee_player' !== $post->post_type ) {
			return;
		}

		if ( ! $this->can_save_meta( $post_id, 'cee_player_meta_nonce', 'cee_player_meta' ) ) {
			return;
		}

		$number   = isset( $_POST['cee_player_number'] ) ? max( 0, absint( $_POST['cee_player_number'] ) ) : '';
		$position = isset( $_POST['cee_player_position'] ) ? sanitize_text_field( wp_unslash( $_POST['cee_player_position'] ) ) : '';
		$user_id  = isset( $_POST['cee_player_user_id'] ) ? absint( $_POST['cee_player_user_id'] ) : 0;

                $team_ids = array();
                if ( isset( $_POST['cee_player_teams'] ) && is_array( $_POST['cee_player_teams'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified earlier.
                        foreach ( $_POST['cee_player_teams'] as $team_id ) {
                                $team_ids[] = absint( $team_id );
                        }
                }

                $team_ids = array_values( array_unique( array_filter( $team_ids ) ) );

                update_post_meta( $post_id, '_cee_player_number', '' === $number ? '' : $number );
                update_post_meta( $post_id, '_cee_player_position', $position );
                update_post_meta( $post_id, '_cee_player_user_id', $user_id );

                if ( $this->assignment ) {
                        $this->assignment->sync_player_teams( $post_id, $team_ids );
                } else {
                        update_post_meta( $post_id, '_cee_player_teams', $team_ids );
                }
        }

	/**
	 * Save venue meta.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 *
	 * @return void
	 */
	public function save_venue_meta( $post_id, $post ) {
		if ( 'cee_venue' !== $post->post_type ) {
			return;
		}

		if ( ! $this->can_save_meta( $post_id, 'cee_venue_meta_nonce', 'cee_venue_meta' ) ) {
			return;
		}

		$address  = isset( $_POST['cee_venue_address'] ) ? sanitize_text_field( wp_unslash( $_POST['cee_venue_address'] ) ) : '';
		$map_link = isset( $_POST['cee_venue_map_link'] ) ? esc_url_raw( wp_unslash( $_POST['cee_venue_map_link'] ) ) : '';

		update_post_meta( $post_id, '_cee_venue_address', $address );
		update_post_meta( $post_id, '_cee_venue_map_link', $map_link );
	}

	/**
	 * Render add form fields for season taxonomy.
	 *
	 * @return void
	 */
	public function render_season_add_fields() {
		$nonce_action = 'cee_season_meta';
		wp_nonce_field( $nonce_action, 'cee_season_meta_nonce' );

		$products = $this->get_woocommerce_products();
		$product_id = 0;
		$is_edit   = false;

		include CEE_PLUGIN_DIR . 'admin/views/tax-season-meta.php';
	}

	/**
	 * Render edit form fields for season taxonomy.
	 *
	 * @param WP_Term $term Current term.
	 * @param string  $taxonomy Taxonomy slug.
	 *
	 * @return void
	 */
	public function render_season_edit_fields( $term, $taxonomy ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$nonce_action = 'cee_season_meta';
		wp_nonce_field( $nonce_action, 'cee_season_meta_nonce' );
		$product_id = absint( get_term_meta( $term->term_id, '_cee_season_wc_product_id', true ) );
		$products   = $this->get_woocommerce_products();
		$is_edit    = true;

		include CEE_PLUGIN_DIR . 'admin/views/tax-season-meta.php';
	}

	/**
	 * Save season term meta.
	 *
	 * @param int $term_id Term ID.
	 *
	 * @return void
	 */
        public function save_season_meta( $term_id ) {
                if ( ! isset( $_POST['cee_season_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cee_season_meta_nonce'] ) ), 'cee_season_meta' ) ) {
                        return;
                }

                if ( ! current_user_can( 'manage_options' ) ) {
                        return;
                }

                $product_id = isset( $_POST['cee_season_wc_product_id'] ) ? absint( $_POST['cee_season_wc_product_id'] ) : 0;
                update_term_meta( $term_id, '_cee_season_wc_product_id', $product_id );
        }

        /**
         * Sanitize event date.
         *
         * @param string $value Raw value.
         *
         * @return string
         */
        public static function sanitize_event_date( $value ) {
                $value = is_string( $value ) ? trim( $value ) : '';
                if ( '' === $value ) {
                        return '';
                }

                $parts = explode( '-', $value );
                if ( 3 !== count( $parts ) ) {
                        return '';
                }

                $year  = (int) $parts[0];
                $month = (int) $parts[1];
                $day   = (int) $parts[2];

                if ( ! wp_checkdate( $month, $day, $year, $value ) ) {
                        return '';
                }

                return sprintf( '%04d-%02d-%02d', $year, $month, $day );
        }

        /**
         * Sanitize event time.
         *
         * @param string $value Raw value.
         *
         * @return string
         */
        public static function sanitize_event_time( $value ) {
                $value = is_string( $value ) ? trim( $value ) : '';
                if ( '' === $value ) {
                        return '';
                }

                if ( preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value ) ) {
                        return $value;
                }

                return '';
        }

	/**
	 * Check whether meta can be saved.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $nonce_field Field name.
	 * @param string $nonce_action Action name.
	 *
	 * @return bool
	 */
	private function can_save_meta( $post_id, $nonce_field, $nonce_action ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		if ( ! isset( $_POST[ $nonce_field ] ) ) {
			return false;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST[ $nonce_field ] ) );
		if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieve posts for select dropdowns.
	 *
	 * @param string $post_type Post type.
	 *
	 * @return array
	 */
	private function get_posts_for_select( $post_type ) {
		$args  = array(
			'post_type'      => $post_type,
			'posts_per_page' => 200,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'ids',
		);
		$query = new WP_Query( $args );
		$data  = array();

		foreach ( $query->posts as $id ) {
			$data[ $id ] = get_the_title( $id );
		}

		return $data;
	}

	/**
	 * Retrieve users for select options.
	 *
	 * @return array
	 */
	private function get_users_for_select() {
		$users = get_users(
			array(
				'fields' => array( 'ID', 'display_name', 'user_email' ),
			)
		);

		$data = array( 0 => __( '— Aucun utilisateur —', 'club-easy-event' ) );

		foreach ( $users as $user ) {
			$data[ $user->ID ] = sprintf( '%1$s (%2$s)', $user->display_name, $user->user_email );
		}

		return $data;
	}

	/**
	 * Retrieve WooCommerce products for dropdown.
	 *
	 * @return array
	 */
	private function get_woocommerce_products() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return array();
		}

		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => 100,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'ids',
		);

		$query = new WP_Query( $args );
		$data  = array();

		foreach ( $query->posts as $id ) {
			$data[ $id ] = get_the_title( $id );
		}

		return $data;
	}

	/**
	 * Retrieve available event types.
	 *
	 * @return array
	 */
	public static function get_event_types() {
		return array(
			'match'    => __( 'Match', 'club-easy-event' ),
			'training' => __( 'Entraînement', 'club-easy-event' ),
			'social'   => __( 'Événement social', 'club-easy-event' ),
		);
	}

	/**
	 * Determine the canonical event type key from stored value.
	 *
	 * @param string $value Stored value.
	 *
	 * @return string
	 */
	public static function get_event_type_key( $value ) {
		if ( '' === $value ) {
			return '';
		}

		$types  = self::get_event_types();
		$value  = (string) $value;
		$legacy = self::get_legacy_event_type_map();

		if ( isset( $types[ $value ] ) ) {
			return $value;
		}

		if ( isset( $legacy[ $value ] ) ) {
			return $legacy[ $value ];
		}

		$key = array_search( $value, $types, true );
		if ( false !== $key ) {
			return $key;
		}

		return '';
	}

	/**
	 * Get the display label for an event type value.
	 *
	 * @param string $value Stored value.
	 *
	 * @return string
	 */
	public static function get_event_type_label( $value ) {
		if ( '' === $value ) {
			return '';
		}

		$types  = self::get_event_types();
		$legacy = self::get_legacy_event_type_map();

		if ( isset( $types[ $value ] ) ) {
			return $types[ $value ];
		}

		if ( isset( $legacy[ $value ] ) && isset( $types[ $legacy[ $value ] ] ) ) {
			return $types[ $legacy[ $value ] ];
		}

		if ( in_array( $value, $types, true ) ) {
			return $value;
		}

		return $value;
	}

	/**
	 * Legacy map for event type values stored before slug usage.
	 *
	 * @return array
	 */
	private static function get_legacy_event_type_map() {
		return array(
			'Match'             => 'match',
			'Entraînement'      => 'training',
			'Événement social' => 'social',
		);
	}

}
