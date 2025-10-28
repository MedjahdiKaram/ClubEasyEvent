<?php
/**
 * Core plugin orchestrator.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin controller.
 */
class CEE_Plugin {

	/**
	 * Loader instance.
	 *
	 * @var CEE_Loader
	 */
	protected $loader;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	protected $plugin_name = 'club-easy-event';

	/**
	 * CPT handler.
	 *
	 * @var CEE_CPT
	 */
	protected $cpt;

	/**
	 * Taxonomy handler.
	 *
	 * @var CEE_Taxonomies
	 */
	protected $taxonomies;

	/**
	 * Meta box handler.
	 *
	 * @var CEE_Meta
	 */
	protected $meta;

        /**
         * Admin columns handler.
         *
         * @var CEE_Admin_Columns
         */
        protected $admin_columns;

        /**
         * Approval workflow manager.
         *
         * @var CEE_Approval
         */
        protected $approval;

        /**
         * Notifications manager.
         *
         * @var CEE_Notifications
         */
        protected $notifications;

        /**
         * Assignment manager.
         *
         * @var CEE_Assignment
         */
        protected $assignment;

	/**
	 * Settings manager.
	 *
	 * @var CEE_Settings
	 */
	protected $settings;

	/**
	 * Shortcode manager.
	 *
	 * @var CEE_Shortcodes
	 */
	protected $shortcodes;

	/**
	 * AJAX controller.
	 *
	 * @var CEE_Ajax
	 */
	protected $ajax;

	/**
	 * Cron controller.
	 *
	 * @var CEE_Cron
	 */
	protected $cron;

	/**
	 * WooCommerce helper.
	 *
	 * @var CEE_WooCommerce
	 */
	protected $woocommerce;

	/**
	 * Roles handler.
	 *
	 * @var CEE_Roles
	 */
	protected $roles;

	/**
	 * Front-end manager.
	 *
	 * @var CEE_Frontend
	 */
	protected $frontend;

/**
 * Admin UI manager.
 *
 * @var CEE_Admin
 */
protected $admin;

/**
 * Internationalization helper.
 *
 * @var CEE_I18n
 */
protected $i18n;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->version = defined( 'CEE_VERSION' ) ? CEE_VERSION : '1.0.0';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load all plugin dependencies.
	 *
	 * @return void
	 */
	private function load_dependencies() {
                require_once CEE_PLUGIN_DIR . 'includes/class-cee-loader.php';
                require_once CEE_PLUGIN_DIR . 'includes/class-cee-cpt.php';
                require_once CEE_PLUGIN_DIR . 'includes/class-cee-taxonomies.php';
                require_once CEE_PLUGIN_DIR . 'includes/class-cee-assignment.php';
                require_once CEE_PLUGIN_DIR . 'includes/class-cee-meta.php';
                require_once CEE_PLUGIN_DIR . 'includes/class-cee-admin-columns.php';
                require_once CEE_PLUGIN_DIR . 'includes/class-cee-onboarding.php';
                require_once CEE_PLUGIN_DIR . 'includes/class-cee-settings.php';
                require_once CEE_PLUGIN_DIR . 'includes/class-cee-shortcodes.php';
                require_once CEE_PLUGIN_DIR . 'includes/class-cee-ajax.php';
                require_once CEE_PLUGIN_DIR . 'includes/class-cee-cron.php';
                require_once CEE_PLUGIN_DIR . 'includes/class-cee-woocommerce.php';
                require_once CEE_PLUGIN_DIR . 'includes/class-cee-roles.php';
                require_once CEE_PLUGIN_DIR . 'includes/class-cee-approval.php';
                require_once CEE_PLUGIN_DIR . 'includes/class-cee-notifications.php';
                require_once CEE_PLUGIN_DIR . 'public/class-cee-frontend.php';
                require_once CEE_PLUGIN_DIR . 'public/shortcodes/class-cee-shortcode-player-signup.php';
                require_once CEE_PLUGIN_DIR . 'admin/class-cee-admin.php';
                require_once CEE_PLUGIN_DIR . 'admin/class-cee-roles-manager.php';

                $this->loader       = new CEE_Loader();
                $this->cpt          = new CEE_CPT();
                $this->taxonomies   = new CEE_Taxonomies();
                $this->assignment   = new CEE_Assignment();
                $this->meta         = new CEE_Meta( $this->assignment );
                $this->admin_columns = new CEE_Admin_Columns();
                $this->settings     = new CEE_Settings();
                $this->woocommerce  = new CEE_WooCommerce();
                $this->frontend     = new CEE_Frontend( $this->plugin_name, $this->version );
                $this->shortcodes   = new CEE_Shortcodes( $this->frontend, $this->woocommerce, $this->settings, $this->assignment );
                $this->ajax         = new CEE_Ajax();
                $this->cron         = new CEE_Cron();
                $this->roles        = new CEE_Roles();
                $this->admin        = new CEE_Admin( $this->settings, $this->admin_columns );
                $this->i18n         = new CEE_I18n();
                $this->approval     = new CEE_Approval();
                $this->notifications = new CEE_Notifications( $this->settings );
	}

	/**
	 * Register text domain loader.
	 *
	 * @return void
	 */
	private function set_locale() {
		$this->loader->add_action( 'plugins_loaded', $this->i18n, 'load_textdomain' );
		$this->loader->add_action( 'init', CEE_I18n::class, 'maybe_switch_locale', 1 );
		$this->loader->add_action( 'shutdown', CEE_I18n::class, 'restore_locale' );
	}

	/**
	 * Define admin-facing hooks.
	 *
	 * @return void
	 */
        private function define_admin_hooks() {
                $this->loader->add_action( 'init', $this->roles, 'register_caps' );
                $this->loader->add_action( 'init', $this->cpt, 'register_post_types' );
                $this->loader->add_action( 'init', $this->taxonomies, 'register_taxonomies' );
                $this->loader->add_action( 'add_meta_boxes', $this->meta, 'register_meta_boxes' );
		$this->loader->add_action( 'save_post_cee_event', $this->meta, 'save_event_meta', 10, 2 );
		$this->loader->add_action( 'save_post_cee_team', $this->meta, 'save_team_meta', 10, 2 );
		$this->loader->add_action( 'save_post_cee_player', $this->meta, 'save_player_meta', 10, 2 );
                $this->loader->add_action( 'save_post_cee_venue', $this->meta, 'save_venue_meta', 10, 2 );
                $this->loader->add_action( 'cee_season_add_form_fields', $this->meta, 'render_season_add_fields' );
                $this->loader->add_action( 'cee_season_edit_form_fields', $this->meta, 'render_season_edit_fields', 10, 2 );
                $this->loader->add_action( 'created_cee_season', $this->meta, 'save_season_meta', 10, 2 );
                $this->loader->add_action( 'edited_cee_season', $this->meta, 'save_season_meta', 10, 2 );

                if ( method_exists( $this->meta, 'register_admin_hooks' ) ) {
                        $this->meta->register_admin_hooks( $this->loader );
                }

                $this->loader->add_action( 'admin_menu', $this->admin, 'register_menus' );
                $this->loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_assets' );
                $this->loader->add_action( 'in_admin_header', CEE_Onboarding::class, 'maybe_render' );
                $this->loader->add_action( 'wp_ajax_cee_onboarding_dismiss', CEE_Onboarding::class, 'ajax_dismiss' );
                $this->loader->add_action( 'admin_init', $this->settings, 'register_settings' );

                $this->loader->add_filter( 'manage_edit-cee_event_columns', $this->admin_columns, 'add_columns' );
                $this->loader->add_action( 'manage_cee_event_posts_custom_column', $this->admin_columns, 'render_column', 10, 2 );
                $this->loader->add_filter( 'manage_edit-cee_event_sortable_columns', $this->admin_columns, 'register_sortable_columns' );
                $this->loader->add_action( 'pre_get_posts', $this->admin_columns, 'handle_sorting' );
                $this->loader->add_filter( 'manage_edit-cee_team_columns', $this->admin_columns, 'add_team_columns' );
                $this->loader->add_action( 'manage_cee_team_posts_custom_column', $this->admin_columns, 'render_team_column', 10, 2 );
                $this->loader->add_filter( 'manage_edit-cee_player_columns', $this->admin_columns, 'add_player_columns' );
                $this->loader->add_action( 'manage_cee_player_posts_custom_column', $this->admin_columns, 'render_player_column', 10, 2 );
                $this->loader->add_filter( 'manage_edit-cee_venue_columns', $this->admin_columns, 'add_venue_columns' );
                $this->loader->add_action( 'manage_cee_venue_posts_custom_column', $this->admin_columns, 'render_venue_column', 10, 2 );

                $this->loader->add_action( 'quick_edit_custom_box', $this->admin, 'quick_edit_date_field', 10, 2 );
                $this->loader->add_action( 'save_post_cee_event', $this->admin, 'save_quick_edit_date', 5 );
                $this->loader->add_action( 'admin_print_footer_scripts-edit.php', $this->admin, 'print_quick_edit_script' );
                $this->loader->add_filter( 'bulk_actions-edit-cee_event', $this->admin, 'register_bulk_actions' );
                $this->loader->add_filter( 'handle_bulk_actions-edit-cee_event', $this->admin, 'handle_bulk_actions', 10, 3 );
                $this->loader->add_action( 'admin_footer-edit.php', $this->admin, 'print_bulk_action_fields' );
                $this->loader->add_action( 'admin_notices', $this->admin, 'render_bulk_action_notice' );

                if ( method_exists( $this->approval, 'register_admin_hooks' ) ) {
                        $this->approval->register_admin_hooks( $this->loader );
                }

                if ( method_exists( $this->assignment, 'register_admin_hooks' ) ) {
                        $this->assignment->register_admin_hooks( $this->loader );
                }

                if ( method_exists( $this->notifications, 'register_admin_hooks' ) ) {
                        $this->notifications->register_admin_hooks( $this->loader );
                }
        }

	/**
	 * Define public-facing hooks.
	 *
	 * @return void
	 */
	private function define_public_hooks() {
		$this->loader->add_action( 'init', $this->shortcodes, 'register_shortcodes' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this->frontend, 'maybe_enqueue_assets' );
		$this->loader->add_filter( 'template_include', $this->frontend, 'maybe_load_templates' );
                $this->loader->add_action( 'wp_ajax_cee_handle_rsvp', $this->ajax, 'handle_rsvp' );
                $this->loader->add_action( 'wp_ajax_nopriv_cee_handle_rsvp', $this->ajax, 'handle_rsvp' );
                $this->loader->add_action( 'cee_daily_reminders', $this->cron, 'send_daily_reminders' );

                if ( method_exists( $this->notifications, 'register_public_hooks' ) ) {
                        $this->notifications->register_public_hooks( $this->loader );
                }
        }

	/**
	 * Execute registered hooks.
	 *
	 * @return void
	 */
	public function run() {
		$this->loader->run();
	}
}
