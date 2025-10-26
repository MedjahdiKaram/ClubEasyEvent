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
			'primary_color'  => '#0d6efd',
		);
	}
}
