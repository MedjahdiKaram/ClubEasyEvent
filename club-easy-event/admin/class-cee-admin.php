<?php
/**
 * Admin functionality.
 *
 * @package ClubEasyEvent\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles admin menus, assets, and dashboards.
 */
class CEE_Admin {

	/**
	 * Settings manager.
	 *
	 * @var CEE_Settings
	 */
	protected $settings;

	/**
	 * Admin columns handler.
	 *
	 * @var CEE_Admin_Columns
	 */
	protected $columns;

	/**
	 * Constructor.
	 *
	 * @param CEE_Settings      $settings Settings manager.
	 * @param CEE_Admin_Columns $columns  Columns handler.
	 */
	public function __construct( CEE_Settings $settings, CEE_Admin_Columns $columns ) {
		$this->settings = $settings;
		$this->columns  = $columns;
	}

	/**
	 * Register plugin admin menu.
	 *
	 * @return void
	 */
	public function register_menus() {
                add_menu_page(
                        __( 'Club Easy Event', 'club-easy-event' ),
                        __( 'Club Easy Event', 'club-easy-event' ),
                        'manage_options',
                        'cee_dashboard',
                        array( $this, 'render_dashboard' ),
                        'dashicons-awards',
                        26
                );

                add_submenu_page(
                        'cee_dashboard',
                        __( 'Événements', 'club-easy-event' ),
                        __( 'Événements', 'club-easy-event' ),
                        'edit_posts',
                        'edit.php?post_type=cee_event'
                );

                add_submenu_page(
                        'cee_dashboard',
                        __( 'Équipes', 'club-easy-event' ),
                        __( 'Équipes', 'club-easy-event' ),
                        'edit_posts',
                        'edit.php?post_type=cee_team'
                );

                add_submenu_page(
                        'cee_dashboard',
                        __( 'Paramètres', 'club-easy-event' ),
                        __( 'Paramètres', 'club-easy-event' ),
                        'manage_options',
                        'cee_settings',
                        array( $this->settings, 'render_settings_page' )
                );
	}

	/**
	 * Enqueue admin assets on relevant screens.
	 *
	 * @param string $hook Current admin page.
	 *
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		$allowed_post_types = array( 'cee_event', 'cee_team', 'cee_player', 'cee_venue' );
		$post_type          = isset( $screen->post_type ) ? $screen->post_type : '';
		$is_settings_page   = 'toplevel_page_cee_dashboard' === $hook || 'club-easy-event_page_cee_settings' === $hook;
		if ( ! in_array( $post_type, $allowed_post_types, true ) && ! $is_settings_page && 'edit-cee_season' !== $screen->id ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-spinner' );

		$inline = <<<'JS'
	jQuery(function($){
		$('#cee_settings_primary_color').wpColorPicker();
		$('.cee-date-field').datepicker({dateFormat:'yy-mm-dd'});
		$('.cee-time-field').each(function(){
			var $input=$(this);
			var initial=$input.val();
			var parsed=parseInt(initial.replace(':',''),10);
			if(!isNaN(parsed)){
				setTimeout(function(){formatValue(parsed);},0);
			}
			function formatValue(value){
				if(isNaN(value)){return;}
				var hours=('0'+Math.floor(value/100)).slice(-2);
				var minutes=('0'+(value%100)).slice(-2);
				$input.val(hours+':'+minutes);
			}
			$input.spinner({min:0,max:2359,step:15,stop:function(){formatValue($input.spinner('value'));}});
		});
	});
JS;
		wp_add_inline_script( 'jquery-ui-spinner', $inline );
	}

	/**
	 * Render dashboard overview.
	 *
	 * @return void
	 */
	public function render_dashboard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Vous n’avez pas la permission d’accéder à cette page.', 'club-easy-event' ) );
		}
		?>
		<div class="wrap">
		<h1><?php esc_html_e( 'Bienvenue dans Club Easy Event', 'club-easy-event' ); ?></h1>
		<p><?php esc_html_e( 'Utilisez le menu pour gérer vos événements, équipes, joueurs et lieux. Configurez les rappels par e-mail et la couleur principale dans les paramètres.', 'club-easy-event' ); ?></p>
		</div>
		<?php
	}

}
