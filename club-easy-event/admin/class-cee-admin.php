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

        const BULK_ACTION_SHIFT_FORWARD  = 'cee_shift_days_forward';
        const BULK_ACTION_SHIFT_BACKWARD = 'cee_shift_days_backward';

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
         * Quick edit nonce flag.
         *
         * @var bool
         */
        protected $quick_edit_nonce_printed = false;

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

                $submenus = array(
                        array(
                                'page_title' => __( 'Événements', 'club-easy-event' ),
                                'menu_title' => __( 'Événements', 'club-easy-event' ),
                                'capability' => 'edit_posts',
                                'menu_slug'  => 'edit.php?post_type=cee_event',
                                'callback'   => '',
                        ),
                        array(
                                'page_title' => __( 'Lieux', 'club-easy-event' ),
                                'menu_title' => __( 'Lieux', 'club-easy-event' ),
                                'capability' => 'edit_posts',
                                'menu_slug'  => 'edit.php?post_type=cee_venue',
                                'callback'   => '',
                        ),
                        array(
                                'page_title' => __( 'Équipes', 'club-easy-event' ),
                                'menu_title' => __( 'Équipes', 'club-easy-event' ),
                                'capability' => 'edit_posts',
                                'menu_slug'  => 'edit.php?post_type=cee_team',
                                'callback'   => '',
                        ),
                        array(
                                'page_title' => __( 'Joueurs', 'club-easy-event' ),
                                'menu_title' => __( 'Joueurs', 'club-easy-event' ),
                                'capability' => 'edit_posts',
                                'menu_slug'  => 'edit.php?post_type=cee_player',
                                'callback'   => '',
                        ),
                        array(
                                'page_title' => __( 'Ligues', 'club-easy-event' ),
                                'menu_title' => __( 'Ligues', 'club-easy-event' ),
                                'capability' => 'manage_categories',
                                'menu_slug'  => 'edit-tags.php?taxonomy=cee_league&post_type=cee_event',
                                'callback'   => '',
                        ),
                        array(
                                'page_title' => __( 'Ajouter un lieu', 'club-easy-event' ),
                                'menu_title' => __( 'Ajouter un lieu', 'club-easy-event' ),
                                'capability' => 'edit_posts',
                                'menu_slug'  => 'post-new.php?post_type=cee_venue',
                                'callback'   => '',
                        ),
                        array(
                                'page_title' => __( 'Paramètres', 'club-easy-event' ),
                                'menu_title' => __( 'Paramètres', 'club-easy-event' ),
                                'capability' => 'manage_options',
                                'menu_slug'  => 'cee_settings',
                                'callback'   => array( $this->settings, 'render_settings_page' ),
                        ),
                        array(
                                'page_title' => __( 'Gestion des rôles', 'club-easy-event' ),
                                'menu_title' => __( 'Gestion des rôles', 'club-easy-event' ),
                                'capability' => 'manage_options',
                                'menu_slug'  => 'cee_roles',
                                'callback'   => array( 'CEE_Roles_Manager', 'render' ),
                        ),
                );

                foreach ( $submenus as $submenu ) {
                        add_submenu_page(
                                'cee_dashboard',
                                $submenu['page_title'],
                                $submenu['menu_title'],
                                $submenu['capability'],
                                $submenu['menu_slug'],
                                $submenu['callback']
                        );
                }
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

                if ( class_exists( 'CEE_Onboarding' ) && CEE_Onboarding::is_plugin_screen() && ! CEE_Onboarding::user_dismissed() ) {
                        wp_enqueue_style( 'cee-onboarding', plugins_url( 'admin/css/cee-onboarding.css', CEE_PLUGIN_FILE ), array(), CEE_VERSION );
                        wp_enqueue_script( 'cee-onboarding', plugins_url( 'admin/js/cee-onboarding.js', CEE_PLUGIN_FILE ), array( 'jquery', 'wp-i18n' ), CEE_VERSION, true );
                        wp_set_script_translations( 'cee-onboarding', 'club-easy-event', plugin_dir_path( CEE_PLUGIN_FILE ) . 'languages' );
                        wp_localize_script(
                                'cee-onboarding',
                                'CEEOnboarding',
                                array(
                                        'ajax_url' => admin_url( 'admin-ajax.php' ),
                                        'nonce'    => wp_create_nonce( 'cee_onboarding_nonce' ),
                                )
                        );
                }

                if ( in_array( $hook, array( 'club-easy-event_page_cee_settings' ), true ) ) {
                        wp_enqueue_style( 'wp-color-picker' );
                        wp_enqueue_script( 'wp-color-picker' );
                        wp_add_inline_script( 'wp-color-picker', 'jQuery(function($){$("#cee_settings_primary_color").wpColorPicker();});' );
                }

                $post_type = isset( $screen->post_type ) ? $screen->post_type : '';

                if ( 'cee_event' === $post_type && in_array( $screen->base, array( 'post', 'post-new' ), true ) ) {
                        wp_enqueue_script( 'jquery-ui-datepicker' );
                        wp_enqueue_script( 'cee-datetime-enhance', plugins_url( 'admin/js/datetime-enhance.js', CEE_PLUGIN_FILE ), array( 'jquery', 'jquery-ui-datepicker', 'wp-i18n' ), CEE_VERSION, true );
                        wp_set_script_translations( 'cee-datetime-enhance', 'club-easy-event', plugin_dir_path( CEE_PLUGIN_FILE ) . 'languages' );
                        wp_enqueue_style( 'cee-datetime-enhance', plugins_url( 'admin/css/datetime-enhance.css', CEE_PLUGIN_FILE ), array(), CEE_VERSION );
                }

                if ( 'cee_event' === $post_type && 'edit' === $screen->base ) {
                        $this->enqueue_shortcode_assets();
                        $this->enqueue_bulk_shift_assets();
                }

                if ( 'cee_team' === $post_type && 'edit' === $screen->base ) {
                        $this->enqueue_shortcode_assets();
                }

                if ( isset( $_GET['page'] ) && 'cee_roles' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
                        wp_enqueue_style( 'cee-roles-manager', plugins_url( 'admin/css/roles-manager.css', CEE_PLUGIN_FILE ), array(), CEE_VERSION );
                        wp_enqueue_script( 'cee-roles-manager', plugins_url( 'admin/js/roles-manager.js', CEE_PLUGIN_FILE ), array( 'jquery', 'wp-i18n' ), CEE_VERSION, true );
                        wp_set_script_translations( 'cee-roles-manager', 'club-easy-event', plugin_dir_path( CEE_PLUGIN_FILE ) . 'languages' );
                        wp_localize_script(
                                'cee-roles-manager',
                                'CEERolesManager',
                                array(
                                        'filterPlaceholder' => __( 'Filtrer les utilisateurs…', 'club-easy-event' ),
                                )
                        );
                }
        }

        /**
         * Enqueue shortcode column helper.
         *
         * @return void
         */
        protected function enqueue_shortcode_assets() {
                static $enqueued = false;
                if ( $enqueued ) {
                        return;
                }
                $enqueued = true;

                wp_enqueue_script( 'wp-i18n' );
                wp_enqueue_script( 'jquery' );
                $script = <<<'JS'
        (function($){
                const __ = wp.i18n.__;
                $(document).on('click', '.cee-shortcode-copy', function(event){
                        event.preventDefault();
                        const $button = $(this);
                        const shortcode = $button.data('shortcode');
                        if (!shortcode) {
                                return;
                        }
                        const original = $button.data('labelOriginal') || $button.text();
                        $button.data('labelOriginal', original);
                        const showSuccess = function(){
                                $button.text(__('Copié !', 'club-easy-event'));
                                setTimeout(function(){
                                        $button.text(original);
                                }, 1500);
                        };
                        const fallbackCopy = function(text){
                                const $temp = $('<textarea class="cee-shortcode-temp" aria-hidden="true"></textarea>').text(text).appendTo('body');
                                $temp[0].select();
                                let success = false;
                                try {
                                        success = document.execCommand('copy');
                                } catch (err) {
                                        success = false;
                                }
                                $temp.remove();
                                if (success) {
                                        showSuccess();
                                } else {
                                        window.alert(__('Copiez le shortcode manuellement.', 'club-easy-event'));
                                }
                        };
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                                navigator.clipboard.writeText(shortcode).then(showSuccess).catch(function(){
                                        fallbackCopy(shortcode);
                                });
                        } else {
                                fallbackCopy(shortcode);
                        }
                });
        })(jQuery);
JS;
                wp_add_inline_script( 'wp-i18n', $script, 'after' );
        }

        /**
         * Enqueue assets for bulk shift helpers.
         *
         * @return void
         */
        protected function enqueue_bulk_shift_assets() {
                static $enqueued = false;
                if ( $enqueued ) {
                        return;
                }
                $enqueued = true;

                wp_enqueue_script( 'wp-i18n' );
                wp_enqueue_script( 'jquery' );
                $script = <<<'JS'
        (function($){
                const __ = wp.i18n.__;
                function handleBulkSubmit(event){
                        const $button = $(event.currentTarget);
                        const $form = $button.closest('form');
                        const selector = $button.is('#doaction') ? $form.find('select[name="action"]') : $form.find('select[name="action2"]');
                        if (!selector.length) {
                                return;
                        }
                        const action = selector.val();
                        if (action !== 'cee_shift_days_forward' && action !== 'cee_shift_days_backward') {
                                return;
                        }
                        event.preventDefault();
                        const promptLabel = action === 'cee_shift_days_forward'
                                ? __('Décaler de combien de jours vers le futur ?', 'club-easy-event')
                                : __('Décaler de combien de jours vers le passé ?', 'club-easy-event');
                        let value = window.prompt(promptLabel, '1');
                        if (null === value) {
                                return;
                        }
                        value = parseInt(value, 10);
                        if (isNaN(value) || value < 0) {
                                window.alert(__('Veuillez saisir un nombre de jours positif.', 'club-easy-event'));
                                return;
                        }
                        $('#cee_shift_days_value').val(value);
                        $form.trigger('submit');
                }
                $('#doaction, #doaction2').on('click', handleBulkSubmit);
        })(jQuery);
JS;
                wp_add_inline_script( 'wp-i18n', $script, 'after' );
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

        /**
         * Output quick edit field for event date.
         *
         * @param string $column_name Column name.
         * @param string $post_type   Post type.
         *
         * @return void
         */
        public function quick_edit_date_field( $column_name, $post_type ) {
                if ( 'cee_event' !== $post_type || 'cee_date' !== $column_name ) {
                        return;
                }

                if ( ! $this->quick_edit_nonce_printed ) {
                        wp_nonce_field( 'cee_quick_edit_date', 'cee_quick_edit_nonce' );
                        $this->quick_edit_nonce_printed = true;
                }
                ?>
                <fieldset class="inline-edit-col-right">
                        <div class="inline-edit-col">
                                <label class="inline-edit-group">
                                        <span class="title"><?php esc_html_e( 'Date de l’événement', 'club-easy-event' ); ?></span>
                                        <span class="input-text-wrap">
                                                <input type="date" name="cee_event_date" id="cee_event_quick_edit_date" value="" pattern="\d{4}-\d{2}-\d{2}" aria-label="<?php echo esc_attr__( 'Date de l’événement', 'club-easy-event' ); ?>" />
                                        </span>
                                </label>
                        </div>
                </fieldset>
                <?php
        }

        /**
         * Save quick edit value.
         *
         * @param int $post_id Post ID.
         *
         * @return void
         */
        public function save_quick_edit_date( $post_id ) {
                if ( ! isset( $_POST['cee_quick_edit_nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Checked below.
                        return;
                }

                $nonce = sanitize_text_field( wp_unslash( $_POST['cee_quick_edit_nonce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Sanitized here.
                if ( ! wp_verify_nonce( $nonce, 'cee_quick_edit_date' ) ) {
                        return;
                }

                if ( ! current_user_can( 'edit_post', $post_id ) ) {
                        return;
                }

                if ( isset( $_POST['cee_event_date'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
                        $event_date = sanitize_text_field( wp_unslash( $_POST['cee_event_date'] ) );
                        $event_date = CEE_Meta::sanitize_event_date( $event_date );
                        update_post_meta( $post_id, '_cee_event_date', $event_date );
                }
        }

        /**
         * Print script for quick edit.
         *
         * @return void
         */
        public function print_quick_edit_script() {
                $screen = get_current_screen();
                if ( ! $screen || 'edit-cee_event' !== $screen->id ) {
                        return;
                }
                ?>
                <script>
                jQuery(function($){
                        const $wp_inline_edit = inlineEditPost.edit;
                        inlineEditPost.edit = function( postId ){
                                $wp_inline_edit.apply( this, arguments );
                                let id = 0;
                                if ( typeof postId === 'object' ) {
                                        id = parseInt( this.getId( postId ), 10 );
                                } else {
                                        id = parseInt( postId, 10 );
                                }
                                if ( ! id ) {
                                        return;
                                }
                                const $row = $( '#post-' + id );
                                const rawDate = $row.find( '.column-cee_date' ).data( 'raw-date' );
                                if ( rawDate ) {
                                        $( '#cee_event_quick_edit_date' ).val( rawDate );
                                }
                        };
                });
                </script>
                <?php
        }

        /**
         * Register bulk actions for event list.
         *
         * @param array $bulk_actions Bulk actions.
         *
         * @return array
         */
        public function register_bulk_actions( $bulk_actions ) {
                $bulk_actions[ self::BULK_ACTION_SHIFT_FORWARD ]  = __( 'Décaler de +N jours', 'club-easy-event' );
                $bulk_actions[ self::BULK_ACTION_SHIFT_BACKWARD ] = __( 'Décaler de −N jours', 'club-easy-event' );
                return $bulk_actions;
        }

        /**
         * Handle bulk action for shifting events.
         *
         * @param string $redirect_to Redirect URL.
         * @param string $action      Current action.
         * @param array  $post_ids    Post IDs.
         *
         * @return string
         */
        public function handle_bulk_actions( $redirect_to, $action, $post_ids ) {
                if ( ! in_array( $action, array( self::BULK_ACTION_SHIFT_FORWARD, self::BULK_ACTION_SHIFT_BACKWARD ), true ) ) {
                        return $redirect_to;
                }

                if ( empty( $post_ids ) ) {
                        return $redirect_to;
                }

                $nonce = isset( $_REQUEST['cee_shift_days_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['cee_shift_days_nonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce checked here.
                if ( ! wp_verify_nonce( $nonce, 'cee_shift_days' ) ) {
                        return add_query_arg( 'cee_shift_days', 'invalid_nonce', $redirect_to );
                }

                $raw_value = isset( $_REQUEST['cee_shift_days_value'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['cee_shift_days_value'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Sanitized here.
                $offset    = (int) $raw_value;

                if ( $offset < 0 ) {
                        $offset = 0;
                }

                if ( 0 === $offset ) {
                        return add_query_arg( 'cee_shift_days', 'no_value', $redirect_to );
                }

                if ( self::BULK_ACTION_SHIFT_BACKWARD === $action ) {
                        $offset = -1 * abs( $offset );
                }

                $updated = 0;
                foreach ( $post_ids as $post_id ) {
                        if ( ! current_user_can( 'edit_post', $post_id ) ) {
                                continue;
                        }

                        $event_date = get_post_meta( $post_id, '_cee_event_date', true );
                        if ( ! $event_date ) {
                                continue;
                        }

                        $timestamp = strtotime( $event_date . ' 00:00:00' );
                        if ( ! $timestamp ) {
                                continue;
                        }

                        $relative     = ( $offset >= 0 ? '+' : '' ) . $offset . ' days';
                        $new_timestamp = strtotime( $relative, $timestamp );
                        if ( ! $new_timestamp ) {
                                continue;
                        }

                        $new_date = gmdate( 'Y-m-d', $new_timestamp );
                        update_post_meta( $post_id, '_cee_event_date', $new_date );
                        $updated++;
                }

                if ( $updated ) {
                        do_action( 'cee_events_bulk_shift_days', $post_ids, $offset );
                }

                return add_query_arg(
                        array(
                                'cee_shift_days'        => $updated ? 'success' : 'no_change',
                                'cee_shift_days_count'  => $updated,
                                'cee_shift_days_offset' => $offset,
                        ),
                        $redirect_to
                );
        }

        /**
         * Print hidden fields for bulk actions.
         *
         * @return void
         */
        public function print_bulk_action_fields() {
                $screen = get_current_screen();
                if ( ! $screen || 'edit-cee_event' !== $screen->id ) {
                        return;
                }
                ?>
                <input type="hidden" name="cee_shift_days_value" id="cee_shift_days_value" value="" />
                <?php wp_nonce_field( 'cee_shift_days', 'cee_shift_days_nonce' ); ?>
                <?php
        }

        /**
         * Display notices for bulk actions.
         *
         * @return void
         */
        public function render_bulk_action_notice() {
                if ( ! isset( $_GET['cee_shift_days'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
                        return;
                }

                $status = sanitize_text_field( wp_unslash( $_GET['cee_shift_days'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
                $count  = isset( $_GET['cee_shift_days_count'] ) ? absint( $_GET['cee_shift_days_count'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
                $offset = isset( $_GET['cee_shift_days_offset'] ) ? intval( $_GET['cee_shift_days_offset'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.

                switch ( $status ) {
                        case 'success':
                                $message = sprintf(
                                        /* translators: 1: Number of events updated. 2: Offset in days. */
                                        _n( '%1$d événement a été décalé de %2$d jour.', '%1$d événements ont été décalés de %2$d jours.', $count, 'club-easy-event' ),
                                        $count,
                                        abs( $offset )
                                );
                                if ( $offset < 0 ) {
                                        $message .= ' ' . esc_html__( 'Décalage vers le passé appliqué.', 'club-easy-event' );
                                } elseif ( $offset > 0 ) {
                                        $message .= ' ' . esc_html__( 'Décalage vers le futur appliqué.', 'club-easy-event' );
                                }
                                $class = 'notice notice-success is-dismissible';
                                break;
                        case 'invalid_nonce':
                                $message = esc_html__( 'Action groupée non autorisée. Veuillez réessayer.', 'club-easy-event' );
                                $class   = 'notice notice-error';
                                break;
                        case 'no_value':
                                $message = esc_html__( 'Veuillez indiquer un nombre de jours supérieur à zéro.', 'club-easy-event' );
                                $class   = 'notice notice-warning';
                                break;
                        default:
                                $message = esc_html__( 'Aucun événement n’a été mis à jour.', 'club-easy-event' );
                                $class   = 'notice notice-info';
                                break;
                }

                printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
        }
}
