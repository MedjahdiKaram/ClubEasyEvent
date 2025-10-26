<?php
/**
 * Public-facing functionality.
 *
 * @package ClubEasyEvent\Public
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages assets and templates on the front-end.
 */
class CEE_Frontend {

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Flag to indicate if assets should be enqueued.
	 *
	 * @var bool
	 */
	protected $should_enqueue = false;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_name Plugin name.
	 * @param string $version     Plugin version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Mark assets as required.
	 *
	 * @return void
	 */
	public function mark_assets_needed() {
		$this->should_enqueue = true;
	}

	/**
	 * Conditionally enqueue front-end assets.
	 *
	 * @return void
	 */
	public function maybe_enqueue_assets() {
		$style_handle  = $this->plugin_name . '-public';
		$script_handle = $this->plugin_name . '-public';

		wp_register_style( $style_handle, CEE_PLUGIN_URL . 'assets/css/public.css', array(), $this->version );
		wp_register_script( $script_handle, CEE_PLUGIN_URL . 'assets/js/public.js', array( 'jquery' ), $this->version, true );

		if ( ! $this->should_enqueue ) {
			global $post;
			if ( is_a( $post, 'WP_Post' ) ) {
				$has_schedule = has_shortcode( $post->post_content, 'cee_schedule' );
				$has_roster   = has_shortcode( $post->post_content, 'cee_roster' );
				if ( $has_schedule || $has_roster ) {
					$this->should_enqueue = true;
				}
			}
		}

		if ( ! $this->should_enqueue ) {
			return;
		}

		$settings    = new CEE_Settings();
		$primary     = $settings->get_primary_color();
		$inline_css  = ':root{--cee-primary:' . esc_attr( $primary ) . ';}';
		wp_enqueue_style( $style_handle );
		wp_add_inline_style( $style_handle, $inline_css );

		wp_enqueue_script( $script_handle );
		wp_localize_script(
			$script_handle,
			'CEE_Public',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'cee_rsvp_nonce' ),
				'i18n'     => array(
					'success' => __( 'Merci pour votre réponse!', 'club-easy-event' ),
					'error'   => __( 'Une erreur est survenue. Veuillez réessayer.', 'club-easy-event' ),
				),
			)
		);
	}

	/**
	 * Load plugin templates when theme overrides are absent.
	 *
	 * @param string $template Current template.
	 *
	 * @return string
	 */
	public function maybe_load_templates( $template ) {
		if ( is_singular( 'cee_event' ) ) {
			return $this->locate_template( 'single-cee_event.php', $template );
		}

		if ( is_singular( 'cee_team' ) ) {
			return $this->locate_template( 'single-cee_team.php', $template );
		}

		return $template;
	}

	/**
	 * Locate template file, allowing theme overrides.
	 *
	 * @param string $template_name Template name.
	 * @param string $default       Default template.
	 *
	 * @return string
	 */
	private function locate_template( $template_name, $default ) {
		$theme_template = locate_template( array( 'club-easy-event/' . $template_name ) );
		if ( $theme_template ) {
			return $theme_template;
		}

		$plugin_template = CEE_PLUGIN_DIR . 'public/templates/' . $template_name;
		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}

		return $default;
	}
}
