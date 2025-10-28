<?php
/**
 * Approval workflow management.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

/**
 * Handles approval workflow for custom post types.
 */
class CEE_Approval {

        const META_STATE      = '_cee_approval_state';
        const META_REVIEWER   = '_cee_approved_by';
        const META_REVIEWED_AT = '_cee_approved_at';
        const META_NOTE       = '_cee_approval_note';
        const BULK_TRANSIENT  = 'cee_approval_bulk_notice';

        /**
         * Post types supporting approval workflow.
         *
         * @var array
         */
        protected $post_types = array();

        /**
         * Constructor.
         */
        public function __construct() {
                $this->post_types = apply_filters(
                        'cee_approval_post_types',
                        array( 'cee_event', 'cee_team', 'cee_player', 'cee_venue' )
                );
        }

        /**
         * Register admin hooks.
         *
         * @param CEE_Loader $loader Loader instance.
         *
         * @return void
         */
        public function register_admin_hooks( CEE_Loader $loader ) {
                $loader->add_action( 'add_meta_boxes', $this, 'register_meta_boxes', 20, 2 );
                $loader->add_action( 'save_post', $this, 'handle_save', 20, 2 );
                $loader->add_action( 'admin_notices', $this, 'render_admin_notice' );
                $loader->add_action( 'restrict_manage_posts', $this, 'render_filters' );
                $loader->add_action( 'pre_get_posts', $this, 'filter_admin_query' );

                foreach ( $this->post_types as $post_type ) {
                        $loader->add_filter( 'bulk_actions-edit-' . $post_type, $this, 'register_bulk_actions' );
                        $loader->add_filter( 'handle_bulk_actions-edit-' . $post_type, $this, 'handle_bulk_action', 10, 3 );
                        $loader->add_action( 'admin_footer-edit.php', $this, 'print_bulk_fields' );
                        $loader->add_filter( 'views_edit-' . $post_type, $this, 'register_views' );
                }
        }

        /**
         * Register approval meta box.
         *
         * @param string  $post_type Post type.
         * @param WP_Post $post      Post object.
         *
         * @return void
         */
        public function register_meta_boxes( $post_type, $post ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
                if ( ! in_array( $post_type, $this->post_types, true ) ) {
                        return;
                }

                add_meta_box(
                        'cee-approval-box',
                        __( 'Vérification & approbation', 'club-easy-event' ),
                        array( $this, 'render_meta_box' ),
                        $post_type,
                        'side',
                        'high'
                );
        }

        /**
         * Render approval meta box.
         *
         * @param WP_Post $post Current post.
         *
         * @return void
         */
        public function render_meta_box( $post ) {
                $state        = self::get_state( $post->ID );
                $states       = self::get_states();
                $can_approve  = $this->user_can_transition( $post->ID, 'approved' );
                $can_reject   = $this->user_can_transition( $post->ID, 'rejected' );
                $can_pending  = $this->user_can_transition( $post->ID, 'pending' );
                $note         = get_post_meta( $post->ID, self::META_NOTE, true );
                $approved_by  = get_post_meta( $post->ID, self::META_REVIEWER, true );
                $approved_at  = get_post_meta( $post->ID, self::META_REVIEWED_AT, true );
                $reviewer     = $approved_by ? get_user_by( 'id', $approved_by ) : false;
                $reviewer_name = $reviewer ? $reviewer->display_name : '';

                wp_nonce_field( 'cee_approval_meta', 'cee_approval_nonce' );

                echo '<p class="cee-approval-state-description">' . esc_html__( 'Définissez l’état de validation pour contrôler la publication et la visibilité.', 'club-easy-event' ) . '</p>';
                echo '<label for="cee_approval_state" class="screen-reader-text">' . esc_html__( 'État d’approbation', 'club-easy-event' ) . '</label>';
                echo '<select id="cee_approval_state" name="cee_approval_state" class="widefat">';
                foreach ( $states as $key => $label ) {
                        $disabled = '';
                        if ( 'approved' === $key && ! $can_approve ) {
                                $disabled = ' disabled="disabled"';
                        }
                        if ( 'rejected' === $key && ! $can_reject ) {
                                $disabled = ' disabled="disabled"';
                        }
                        if ( 'pending' === $key && ! $can_pending ) {
                                $disabled = ' disabled="disabled"';
                        }
                        printf(
                                '<option value="%1$s" %2$s %3$s>%4$s</option>',
                                esc_attr( $key ),
                                selected( $state, $key, false ),
                                $disabled,
                                esc_html( $label )
                        );
                }
                echo '</select>';

                echo '<p class="cee-approval-actions">';
                if ( $can_approve ) {
                        printf(
                                '<button type="submit" class="button button-primary" name="cee_approval_action" value="approve">%s</button> ',
                                esc_html__( 'Marquer comme approuvé', 'club-easy-event' )
                        );
                }
                if ( $can_reject ) {
                        printf(
                                '<button type="submit" class="button" name="cee_approval_action" value="reject" data-toggle="cee-approval-note">%s</button>',
                                esc_html__( 'Rejeter', 'club-easy-event' )
                        );
                }
                echo '</p>';

                printf(
                        '<textarea name="cee_approval_note" id="cee_approval_note" class="widefat cee-approval-note" rows="3" placeholder="%1$s" %2$s>%3$s</textarea>',
                        esc_attr__( 'Ajouter une note pour l’auteur ou l’équipe.', 'club-easy-event' ),
                        $can_reject ? '' : ' readonly="readonly"',
                        esc_textarea( $note )
                );
                echo '<p class="description cee-approval-note-help">' . esc_html__( 'Les notes sont visibles par les éditeurs disposant des droits appropriés.', 'club-easy-event' ) . '</p>';

                if ( $approved_by && $approved_at ) {
                        printf(
                                '<p class="cee-approval-last-review">%1$s <strong>%2$s</strong> %3$s</p>',
                                esc_html__( 'Approuvé par', 'club-easy-event' ),
                                esc_html( $reviewer_name ),
                                esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $approved_at ) ) )
                        );
                }
        }

        /**
         * Handle save_post for approval meta.
         *
         * @param int     $post_id Post ID.
         * @param WP_Post $post    Post object.
         *
         * @return void
         */
        public function handle_save( $post_id, $post ) {
                if ( ! in_array( $post->post_type, $this->post_types, true ) ) {
                        return;
                }

                if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                        return;
                }

                if ( ! isset( $_POST['cee_approval_nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Checked below.
                        return;
                }

                $nonce = sanitize_text_field( wp_unslash( $_POST['cee_approval_nonce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Sanitized here.
                if ( ! wp_verify_nonce( $nonce, 'cee_approval_meta' ) ) {
                        return;
                }

                if ( ! current_user_can( 'edit_post', $post_id ) ) {
                        return;
                }

                $requested_state = isset( $_POST['cee_approval_state'] ) ? sanitize_text_field( wp_unslash( $_POST['cee_approval_state'] ) ) : '';
                $action          = isset( $_POST['cee_approval_action'] ) ? sanitize_text_field( wp_unslash( $_POST['cee_approval_action'] ) ) : '';
                $note            = isset( $_POST['cee_approval_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cee_approval_note'] ) ) : '';

                if ( 'approve' === $action ) {
                        $requested_state = 'approved';
                } elseif ( 'reject' === $action ) {
                        $requested_state = 'rejected';
                }

                $requested_state = $this->sanitize_state( $requested_state );
                if ( ! $requested_state ) {
                        return;
                }

                if ( ! $this->user_can_transition( $post_id, $requested_state ) ) {
                        return;
                }

                $this->update_state( $post_id, $requested_state, $note );
        }

        /**
         * Register bulk actions.
         *
         * @param array $actions Existing actions.
         *
         * @return array
         */
        public function register_bulk_actions( $actions ) {
                $actions['cee_approval_approve'] = __( 'Approuver', 'club-easy-event' );
                $actions['cee_approval_reject']  = __( 'Rejeter', 'club-easy-event' );
                return $actions;
        }

        /**
         * Print bulk action hidden fields.
         *
         * @return void
         */
        public function print_bulk_fields() {
                $screen = get_current_screen();
                if ( ! $screen || ! in_array( $screen->post_type, $this->post_types, true ) ) {
                        return;
                }
                wp_nonce_field( 'cee_approval_bulk', '_cee_approval_bulk_nonce' );
        }

        /**
         * Handle bulk actions.
         *
         * @param string $redirect_to Redirect URL.
         * @param string $doaction    Action key.
         * @param array  $post_ids    Selected post IDs.
         *
         * @return string
         */
        public function handle_bulk_action( $redirect_to, $doaction, $post_ids ) {
                if ( ! in_array( $doaction, array( 'cee_approval_approve', 'cee_approval_reject' ), true ) ) {
                        return $redirect_to;
                }

                if ( ! isset( $_REQUEST['_cee_approval_bulk_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_cee_approval_bulk_nonce'] ) ), 'cee_approval_bulk' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified here.
                        return $redirect_to;
                }

                $desired_state = 'cee_approval_approve' === $doaction ? 'approved' : 'rejected';
                if ( ! $this->current_user_can_for_state( $desired_state ) ) {
                        return $redirect_to;
                }

                $changed = 0;
                foreach ( $post_ids as $post_id ) {
                        if ( ! current_user_can( 'edit_post', $post_id ) ) {
                                continue;
                        }
                        if ( $this->update_state( $post_id, $desired_state ) ) {
                                $changed++;
                        }
                }

                if ( $changed ) {
                        set_transient(
                                self::BULK_TRANSIENT,
                                array(
                                        'state' => $desired_state,
                                        'count' => $changed,
                                ),
                                MINUTE_IN_SECONDS
                        );
                }

                $redirect_to = add_query_arg( array( 'cee_approval_bulk' => $desired_state ), $redirect_to );
                return $redirect_to;
        }

        /**
         * Render admin notice for bulk actions.
         *
         * @return void
         */
        public function render_admin_notice() {
                if ( ! isset( $_GET['cee_approval_bulk'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
                        return;
                }

                $notice = get_transient( self::BULK_TRANSIENT );
                if ( ! $notice ) {
                        return;
                }

                delete_transient( self::BULK_TRANSIENT );

                $state = isset( $notice['state'] ) ? $notice['state'] : '';
                $count = isset( $notice['count'] ) ? (int) $notice['count'] : 0;

                if ( ! $state || ! $count ) {
                        return;
                }

                $state_label = isset( self::get_states()[ $state ] ) ? self::get_states()[ $state ] : $state;
                printf(
                        '<div class="notice notice-success is-dismissible"><p>%1$s</p></div>',
                        esc_html( sprintf( /* translators: %1$d: count, %2$s: state label. */ __( '%1$d élément(s) ont été mis à jour vers l’état « %2$s ».', 'club-easy-event' ), $count, $state_label ) )
                );
        }

        /**
         * Output approval filters above list tables.
         *
         * @param string $post_type Current post type.
         *
         * @return void
         */
        public function render_filters( $post_type ) {
                if ( ! in_array( $post_type, $this->post_types, true ) ) {
                        return;
                }

                $current = isset( $_GET['approval_state'] ) ? sanitize_text_field( wp_unslash( $_GET['approval_state'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
                echo '<label class="screen-reader-text" for="filter-by-approval">' . esc_html__( 'Filtrer par état d’approbation', 'club-easy-event' ) . '</label>';
                echo '<select id="filter-by-approval" name="approval_state">';
                echo '<option value="">' . esc_html__( 'Tous les états d’approbation', 'club-easy-event' ) . '</option>';
                foreach ( self::get_states() as $key => $label ) {
                        printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $key ), selected( $current, $key, false ), esc_html( $label ) );
                }
                echo '</select>';
        }

        /**
         * Filter admin query by approval state.
         *
         * @param WP_Query $query Query instance.
         *
         * @return void
         */
        public function filter_admin_query( $query ) {
                if ( ! is_admin() || ! $query->is_main_query() ) {
                        return;
                }

                $post_type = $query->get( 'post_type' );
                if ( ! $post_type || ! in_array( $post_type, $this->post_types, true ) ) {
                        return;
                }

                if ( isset( $_GET['approval_state'] ) && '' !== $_GET['approval_state'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
                        $state = sanitize_text_field( wp_unslash( $_GET['approval_state'] ) );
                        $state = $this->sanitize_state( $state );
                        if ( $state ) {
                                $meta_query = $query->get( 'meta_query' );
                                if ( ! is_array( $meta_query ) ) {
                                        $meta_query = array();
                                }
                                $meta_query[] = array(
                                        'key'   => self::META_STATE,
                                        'value' => $state,
                                );
                                $query->set( 'meta_query', $meta_query );
                        }
                }
        }

        /**
         * Register quick view links.
         *
         * @param array $views Existing views.
         *
         * @return array
         */
        public function register_views( $views ) {
                $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
                if ( ! $screen || empty( $screen->post_type ) ) {
                        return $views;
                }

                $current_state = isset( $_GET['approval_state'] ) ? sanitize_text_field( wp_unslash( $_GET['approval_state'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
                $base_url      = remove_query_arg( array( 'approval_state', 'paged' ) );

                foreach ( self::get_states() as $state_key => $state_label ) {
                        $count = $this->count_by_state( $screen->post_type, $state_key );
                        $url   = add_query_arg( 'approval_state', $state_key, $base_url );
                        $class = $current_state === $state_key ? ' class="current"' : '';
                        $views[ 'cee_state_' . $state_key ] = sprintf(
                                '<a href="%1$s"%4$s>%2$s <span class="count">(%3$d)</span></a>',
                                esc_url( $url ),
                                esc_html( $state_label ),
                                (int) $count,
                                $class
                        );
                }

                return $views;
        }

        /**
         * Count posts by approval state.
         *
         * @param string $post_type Post type.
         * @param string $state     State.
         *
         * @return int
         */
        protected function count_by_state( $post_type, $state ) {
                $query = new WP_Query(
                        array(
                                'post_type'      => $post_type,
                                'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
                                'posts_per_page' => 1,
                                'fields'         => 'ids',
                                'meta_query'     => array(
                                        array(
                                                'key'   => self::META_STATE,
                                                'value' => $state,
                                        ),
                                ),
                        )
                );

                return (int) $query->found_posts;
        }

        /**
         * Update approval state.
         *
         * @param int    $post_id Post ID.
         * @param string $state   New state.
         * @param string $note    Optional note.
         *
         * @return bool
         */
        protected function update_state( $post_id, $state, $note = '' ) {
                $state = $this->sanitize_state( $state );
                if ( ! $state ) {
                        return false;
                }

                $current_state = self::get_state( $post_id );
                if ( $current_state === $state ) {
                        if ( 'rejected' === $state ) {
                                update_post_meta( $post_id, self::META_NOTE, $note );
                        }
                        return false;
                }

                update_post_meta( $post_id, self::META_STATE, $state );

                if ( 'approved' === $state ) {
                        update_post_meta( $post_id, self::META_REVIEWER, get_current_user_id() );
                        update_post_meta( $post_id, self::META_REVIEWED_AT, current_time( 'mysql' ) );
                        delete_post_meta( $post_id, self::META_NOTE );
                } elseif ( 'rejected' === $state ) {
                        if ( '' !== $note ) {
                                update_post_meta( $post_id, self::META_NOTE, $note );
                        }
                        delete_post_meta( $post_id, self::META_REVIEWER );
                        delete_post_meta( $post_id, self::META_REVIEWED_AT );
                } else {
                        delete_post_meta( $post_id, self::META_REVIEWER );
                        delete_post_meta( $post_id, self::META_REVIEWED_AT );
                        if ( '' !== $note ) {
                                update_post_meta( $post_id, self::META_NOTE, $note );
                        } else {
                                delete_post_meta( $post_id, self::META_NOTE );
                        }
                }

                do_action( 'cee_approval_state_changed', $post_id, $state, $current_state );

                return true;
        }

        /**
         * Determine if current user can transition to state.
         *
         * @param int    $post_id Post ID.
         * @param string $state   Desired state.
         *
         * @return bool
         */
        protected function user_can_transition( $post_id, $state ) {
                $state = $this->sanitize_state( $state );
                if ( ! $state ) {
                        return false;
                }

                $has_cap = $this->current_user_can_for_state( $state, $post_id );

                /**
                 * Filter whether the current user can transition the approval state.
                 *
                 * @param bool $has_cap Whether the user can change the state.
                 * @param int  $post_id Post ID.
                 * @param string $state Desired state.
                 */
                return apply_filters( 'cee_can_approve', $has_cap, $post_id, $state );
        }

        /**
         * Check capability for a state.
         *
         * @param string   $state   State key.
         * @param int|bool $post_id Optional post ID.
         *
         * @return bool
         */
        protected function current_user_can_for_state( $state, $post_id = false ) {
                $cap_map = array(
                        'approved' => 'cee_approve_content',
                        'pending'  => 'cee_mark_pending',
                        'rejected' => 'cee_reject_content',
                        'draft'    => 'edit_post',
                );

                if ( isset( $cap_map[ $state ] ) ) {
                        $capability = $cap_map[ $state ];
                        if ( 'edit_post' === $capability ) {
                                return $post_id ? current_user_can( 'edit_post', $post_id ) : current_user_can( 'edit_posts' );
                        }
                        return current_user_can( $capability );
                }

                return current_user_can( 'edit_posts' );
        }

        /**
         * Get sanitized approval state.
         *
         * @param string $state Raw state.
         *
         * @return string
         */
        protected function sanitize_state( $state ) {
                $state = strtolower( (string) $state );
                $states = array_keys( self::get_states() );
                if ( in_array( $state, $states, true ) ) {
                        return $state;
                }
                return '';
        }

        /**
         * Retrieve current state for post.
         *
         * @param int $post_id Post ID.
         *
         * @return string
         */
        public static function get_state( $post_id ) {
                $state = get_post_meta( $post_id, self::META_STATE, true );
                return $state ? $state : 'draft';
        }

        /**
         * State labels.
         *
         * @return array
         */
        public static function get_states() {
                return array(
                        'draft'    => __( 'Brouillon', 'club-easy-event' ),
                        'pending'  => __( 'En attente', 'club-easy-event' ),
                        'approved' => __( 'Approuvé', 'club-easy-event' ),
                        'rejected' => __( 'Rejeté', 'club-easy-event' ),
                );
        }

        /**
         * Render badge HTML for state.
         *
         * @param string $state State key.
         *
         * @return string
         */
        public static function get_state_badge( $state ) {
                $state = strtolower( (string) $state );
                $states = self::get_states();
                if ( ! isset( $states[ $state ] ) ) {
                        return '';
                }

                return sprintf(
                        '<span class="cee-approval-badge cee-approval-%1$s">%2$s</span>',
                        esc_attr( $state ),
                        esc_html( $states[ $state ] )
                );
        }
}
