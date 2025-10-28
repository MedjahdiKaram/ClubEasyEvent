<?php
/**
 * Settings manager.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin settings registration and rendering.
 */
class CEE_Settings {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	private $option_name = 'cee_settings';

	/**
	 * Register settings and sections.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting( 'cee_settings_group', $this->option_name, array( $this, 'sanitize_settings' ) );

		add_settings_section( 'cee_email_section', __( 'Rappels par e-mail', 'club-easy-event' ), '__return_false', 'cee_settings' );
		add_settings_field( 'email_template', __( 'Modèle d’e-mail', 'club-easy-event' ), array( $this, 'render_email_template_field' ), 'cee_settings', 'cee_email_section' );

                add_settings_section( 'cee_style_section', __( 'Style', 'club-easy-event' ), '__return_false', 'cee_settings' );
                add_settings_field( 'primary_color', __( 'Couleur principale (hex)', 'club-easy-event' ), array( $this, 'render_primary_color_field' ), 'cee_settings', 'cee_style_section' );

                add_settings_section( 'cee_onboarding_section', __( 'Tutoriel', 'club-easy-event' ), '__return_false', 'cee_settings' );
                add_settings_field( 'reactivate_onboarding', __( 'Réactiver le tutoriel', 'club-easy-event' ), array( $this, 'render_reactivate_onboarding_field' ), 'cee_settings', 'cee_onboarding_section' );

                add_settings_section( 'cee_signup_section', __( 'Inscription joueurs', 'club-easy-event' ), '__return_false', 'cee_settings' );
                add_settings_field( 'default_signup_team_id', __( 'Équipe par défaut', 'club-easy-event' ), array( $this, 'render_default_team_field' ), 'cee_settings', 'cee_signup_section' );
                add_settings_field( 'signup_success_message', __( 'Message de succès', 'club-easy-event' ), array( $this, 'render_success_message_field' ), 'cee_settings', 'cee_signup_section' );
                add_settings_field( 'event_update_subject', __( 'Sujet des notifications d’événement', 'club-easy-event' ), array( $this, 'render_event_update_subject_field' ), 'cee_settings', 'cee_signup_section' );
                add_settings_field( 'event_update_template', __( 'Modèle d’e-mail de notification', 'club-easy-event' ), array( $this, 'render_event_update_template_field' ), 'cee_settings', 'cee_signup_section' );
                add_settings_field( 'notify_on_event_update', __( 'Notifications de mise à jour d’événement', 'club-easy-event' ), array( $this, 'render_notify_event_field' ), 'cee_settings', 'cee_signup_section' );
                add_settings_field( 'signup_auto_activate', __( 'Activation automatique des joueurs', 'club-easy-event' ), array( $this, 'render_auto_activate_field' ), 'cee_settings', 'cee_signup_section' );
        }

	/**
	 * Sanitize options before saving.
	 *
	 * @param array $input Submitted values.
	 *
	 * @return array
	 */
	public function sanitize_settings( $input ) {
		$defaults = $this->get_default_settings();
		$output   = wp_parse_args( array(), $defaults );

		if ( isset( $input['email_template'] ) ) {
			$output['email_template'] = wp_kses_post( wp_unslash( $input['email_template'] ) );
		}

                if ( isset( $input['primary_color'] ) ) {
                        $color = sanitize_hex_color( $input['primary_color'] );
                        $output['primary_color'] = $color ? $color : $defaults['primary_color'];
                }

                if ( isset( $input['reactivate_onboarding'] ) && '1' === $input['reactivate_onboarding'] && is_user_logged_in() ) {
                        delete_user_meta( get_current_user_id(), CEE_Onboarding::META_KEY );
                }

                unset( $output['reactivate_onboarding'] );

                if ( isset( $input['default_signup_team_id'] ) ) {
                        $output['default_signup_team_id'] = absint( $input['default_signup_team_id'] );
                }

                if ( isset( $input['signup_success_message'] ) ) {
                        $output['signup_success_message'] = wp_kses_post( wp_unslash( $input['signup_success_message'] ) );
                }

                if ( isset( $input['event_update_subject'] ) ) {
                        $output['event_update_subject'] = sanitize_text_field( wp_unslash( $input['event_update_subject'] ) );
                }

                if ( isset( $input['event_update_template'] ) ) {
                        $output['event_update_template'] = wp_kses_post( wp_unslash( $input['event_update_template'] ) );
                }

                $output['notify_on_event_update'] = isset( $input['notify_on_event_update'] ) && '1' === $input['notify_on_event_update'] ? '1' : '0';
                $output['signup_auto_activate']   = isset( $input['signup_auto_activate'] ) && '1' === $input['signup_auto_activate'] ? '1' : '0';

                return $output;
        }

	/**
	 * Render settings page wrapper.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		include CEE_PLUGIN_DIR . 'admin/views/settings-page.php';
	}

	/**
	 * Render email template field.
	 *
	 * @return void
	 */
	public function render_email_template_field() {
		$settings = $this->get_settings();
		$value    = isset( $settings['email_template'] ) ? $settings['email_template'] : '';
		printf(
			'<textarea id="cee_settings_email_template" name="%1$s[email_template]" rows="7" class="large-text code">%2$s</textarea><p class="description">%3$s</p>',
			esc_attr( $this->option_name ),
			esc_textarea( $value ),
			esc_html__( 'Balises disponibles: {event_name}, {event_date}, {event_time}, {event_link}, {team_name}, {venue}, {user_name}', 'club-easy-event' )
		);
	}

	/**
	 * Render primary color field.
	 *
	 * @return void
	 */
        public function render_primary_color_field() {
                $settings = $this->get_settings();
                $value    = isset( $settings['primary_color'] ) ? $settings['primary_color'] : '#0d6efd';
                printf(
                        '<input type="text" id="cee_settings_primary_color" name="%1$s[primary_color]" value="%2$s" class="regular-text" />',
                        esc_attr( $this->option_name ),
                        esc_attr( $value )
                );
        }

        /**
         * Render onboarding reactivation field.
         *
         * @return void
         */
        public function render_reactivate_onboarding_field() {
                if ( ! is_user_logged_in() ) {
                        esc_html_e( 'Veuillez vous connecter pour modifier ce réglage.', 'club-easy-event' );
                        return;
                }

                $field_id    = 'cee_settings_reactivate_onboarding';
                $description = __( 'Le tutoriel est actuellement actif pour votre compte.', 'club-easy-event' );

                if ( class_exists( 'CEE_Onboarding' ) && CEE_Onboarding::user_dismissed() ) {
                        $description = __( 'Le tutoriel est actuellement masqué pour votre compte.', 'club-easy-event' );
                }

                printf(
                        '<label for="%1$s"><input type="checkbox" id="%1$s" name="%2$s[reactivate_onboarding]" value="1" /> %3$s</label>',
                        esc_attr( $field_id ),
                        esc_attr( $this->option_name ),
                        esc_html__( 'Afficher de nouveau le tutoriel lors de ma prochaine visite.', 'club-easy-event' )
                );
                printf( '<p class="description">%s</p>', esc_html( $description ) );
        }

        /**
         * Render default team field.
         *
         * @return void
         */
        public function render_default_team_field() {
                $settings = $this->get_settings();
                $value    = isset( $settings['default_signup_team_id'] ) ? absint( $settings['default_signup_team_id'] ) : 0;
                $teams    = $this->get_team_choices();

                echo '<select id="cee_settings_default_signup_team_id" name="' . esc_attr( $this->option_name ) . '[default_signup_team_id]" class="regular-text">';
                echo '<option value="0">' . esc_html__( '— Aucun —', 'club-easy-event' ) . '</option>';
                foreach ( $teams as $team_id => $label ) {
                        printf( '<option value="%1$d" %2$s>%3$s</option>', absint( $team_id ), selected( $value, $team_id, false ), esc_html( $label ) );
                }
                echo '</select>';
        }

        /**
         * Render success message field.
         *
         * @return void
         */
        public function render_success_message_field() {
                $settings = $this->get_settings();
                $value    = isset( $settings['signup_success_message'] ) ? $settings['signup_success_message'] : $this->get_default_settings()['signup_success_message'];
                printf(
                        '<textarea id="cee_settings_signup_success_message" name="%1$s[signup_success_message]" rows="4" class="large-text">%2$s</textarea><p class="description">%3$s</p>',
                        esc_attr( $this->option_name ),
                        esc_textarea( $value ),
                        esc_html__( 'Utilisez {first_name} pour personnaliser le message.', 'club-easy-event' )
                );
        }

        /**
         * Render event update subject field.
         *
         * @return void
         */
        public function render_event_update_subject_field() {
                $settings = $this->get_settings();
                $value    = isset( $settings['event_update_subject'] ) ? $settings['event_update_subject'] : $this->get_default_settings()['event_update_subject'];
                printf(
                        '<input type="text" id="cee_settings_event_update_subject" name="%1$s[event_update_subject]" value="%2$s" class="regular-text" />',
                        esc_attr( $this->option_name ),
                        esc_attr( $value )
                );
        }

        /**
         * Render event update template field.
         *
         * @return void
         */
        public function render_event_update_template_field() {
                $settings = $this->get_settings();
                $value    = isset( $settings['event_update_template'] ) ? $settings['event_update_template'] : $this->get_default_settings()['event_update_template'];
                printf(
                        '<textarea id="cee_settings_event_update_template" name="%1$s[event_update_template]" rows="5" class="large-text code">%2$s</textarea><p class="description">%3$s</p>',
                        esc_attr( $this->option_name ),
                        esc_textarea( $value ),
                        esc_html__( 'Balises disponibles : {event_name}, {event_date}, {event_time}, {team_home}, {team_away}, {venue}, {event_link}, {changes_list}', 'club-easy-event' )
                );
        }

        /**
         * Render notify event checkbox.
         *
         * @return void
         */
        public function render_notify_event_field() {
                $settings = $this->get_settings();
                $checked  = isset( $settings['notify_on_event_update'] ) ? ( '1' === $settings['notify_on_event_update'] ) : false;
                printf(
                        '<label><input type="checkbox" name="%1$s[notify_on_event_update]" value="1" %2$s /> %3$s</label>',
                        esc_attr( $this->option_name ),
                        checked( $checked, true, false ),
                        esc_html__( 'Envoyer un e-mail aux équipes concernées lorsqu’un événement change (date, heure, lieu, équipes).', 'club-easy-event' )
                );
        }

        /**
         * Render auto activate toggle.
         *
         * @return void
         */
        public function render_auto_activate_field() {
                $settings = $this->get_settings();
                $checked  = isset( $settings['signup_auto_activate'] ) ? ( '1' === $settings['signup_auto_activate'] ) : false;
                printf(
                        '<label><input type="checkbox" name="%1$s[signup_auto_activate]" value="1" %2$s /> %3$s</label><p class="description">%4$s</p>',
                        esc_attr( $this->option_name ),
                        checked( $checked, true, false ),
                        esc_html__( 'Activer automatiquement le joueur après l’inscription.', 'club-easy-event' ),
                        esc_html__( 'Si décoché, les joueurs restent en attente jusqu’à approbation manuelle.', 'club-easy-event' )
                );
        }

	/**
	 * Get settings merged with defaults.
	 *
	 * @return array
	 */
	public function get_settings() {
		$defaults = $this->get_default_settings();
		$settings = get_option( $this->option_name, array() );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Retrieve a single setting value.
	 *
	 * @param string $key Setting key.
	 * @param mixed  $default Default value.
	 *
	 * @return mixed
	 */
	public function get_setting( $key, $default = '' ) {
		$settings = $this->get_settings();
		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}

	/**
	 * Get primary color value.
	 *
	 * @return string
	 */
	public function get_primary_color() {
		$color = $this->get_setting( 'primary_color', '#0d6efd' );
		return apply_filters( 'cee_primary_color', $color );
	}

	/**
	 * Get email template string.
	 *
	 * @return string
	 */
	public function get_email_template() {
		$template = $this->get_setting( 'email_template', '' );
		return apply_filters( 'cee_email_template', $template );
	}

	/**
	 * Default settings.
	 *
	 * @return array
	 */
        private function get_default_settings() {
                return array(
                        'email_template' => __( 'Bonjour {user_name},

Votre prochain événement est {event_name} le {event_date} à {event_time}. Retrouvez toutes les informations ici : {event_link}.

Sportivement,', 'club-easy-event' ),
                        'primary_color'          => '#0d6efd',
                        'default_signup_team_id' => 0,
                        'signup_success_message' => __( 'Merci {first_name}, votre inscription est en attente d’activation par un responsable.', 'club-easy-event' ),
                        'event_update_subject'  => __( '[{site_name}] Mise à jour de l’événement: {event_name}', 'club-easy-event' ),
                        'event_update_template' => __( "Bonjour,\n\nDes modifications ont été appliquées à l’événement {event_name}.\n\n{changes_list}\n\nVoir les détails complets: {event_link}", 'club-easy-event' ),
                        'notify_on_event_update' => '0',
                        'signup_auto_activate'   => '0',
                );
        }

        /**
         * Retrieve team choices for select fields.
         *
         * @return array
         */
        protected function get_team_choices() {
                $query = new WP_Query(
                        array(
                                'post_type'      => 'cee_team',
                                'post_status'    => array( 'publish', 'draft', 'pending' ),
                                'posts_per_page' => 200,
                                'orderby'        => 'title',
                                'order'          => 'ASC',
                                'fields'         => 'ids',
                        )
                );

                $teams = array();
                foreach ( $query->posts as $team_id ) {
                        $teams[ $team_id ] = get_the_title( $team_id );
                }

                return $teams;
        }
}
