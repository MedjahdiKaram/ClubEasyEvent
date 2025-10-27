<?php
/**
 * Roles manager admin page.
 *
 * @package ClubEasyEvent\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

/**
 * Provides UI to assign roles to users.
 */
class CEE_Roles_Manager {

        /**
         * Process incoming actions and render page.
         *
         * @return void
         */
        public static function render() {
                if ( ! current_user_can( 'manage_options' ) ) {
                        wp_die( esc_html__( 'Vous n’avez pas la permission d’accéder à cette page.', 'club-easy-event' ) );
                }

                self::process_actions();

                $notice_data = get_transient( 'cee_roles_manager_notice' );
                if ( $notice_data && is_array( $notice_data ) ) {
                        delete_transient( 'cee_roles_manager_notice' );
                } else {
                        $notice_data = null;
                }

                $paged    = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
                $search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
                $per_page = 20;

                $query_args = array(
                        'number'  => $per_page,
                        'paged'   => $paged,
                        'orderby' => 'display_name',
                        'order'   => 'ASC',
                );

                if ( $search ) {
                        $query_args['search']         = '*' . $search . '*';
                        $query_args['search_columns'] = array( 'user_login', 'user_nicename', 'user_email', 'display_name' );
                }

                $user_query = new WP_User_Query( $query_args );
                $users      = $user_query->get_results();
                $total      = $user_query->get_total();
                $roles      = self::get_allowed_roles();
                $role_names = self::get_role_names( $roles );
                $total_pages = $per_page ? ceil( $total / $per_page ) : 1;

                include CEE_PLUGIN_DIR . 'admin/views/roles-manager.php';
        }

        /**
         * Process single and bulk role actions.
         *
         * @return void
         */
        protected static function process_actions() {
                if ( empty( $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only check.
                        return;
                }

                if ( ! current_user_can( 'manage_options' ) ) {
                        return;
                }

                if ( isset( $_POST['cee_roles_single_update'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Checked below.
                        self::handle_single_update();
                        return;
                }

                if ( isset( $_POST['cee_roles_bulk_submit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Checked below.
                        self::handle_bulk_update();
                        return;
                }
        }

        /**
         * Handle single user update.
         *
         * @return void
         */
        protected static function handle_single_update() {
                $user_id = absint( $_POST['cee_roles_single_update'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Sanitized here.
                if ( ! $user_id ) {
                        return;
                }

                $nonces = isset( $_POST['cee_roles_single_nonce'] ) ? wp_unslash( $_POST['cee_roles_single_nonce'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Sanitized below.
                $nonce  = isset( $nonces[ $user_id ] ) ? sanitize_text_field( $nonces[ $user_id ] ) : '';

                if ( ! wp_verify_nonce( $nonce, 'cee_roles_single_' . $user_id ) ) {
                        self::redirect_with_notice( 'error', __( 'La requête a expiré. Veuillez réessayer.', 'club-easy-event' ) );
                }

                if ( ! current_user_can( 'edit_user', $user_id ) ) {
                        self::redirect_with_notice( 'error', __( 'Vous ne pouvez pas modifier cet utilisateur.', 'club-easy-event' ) );
                }

                $selected_roles = isset( $_POST['cee_roles'][ $user_id ] ) ? (array) wp_unslash( $_POST['cee_roles'][ $user_id ] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Sanitized below.
                self::assign_roles_to_user( $user_id, $selected_roles );

                self::redirect_with_notice( 'updated', __( 'Rôles mis à jour.', 'club-easy-event' ) );
        }

        /**
         * Handle bulk update.
         *
         * @return void
         */
        protected static function handle_bulk_update() {
                if ( empty( $_POST['user_ids'] ) || ! is_array( $_POST['user_ids'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Sanitized below.
                        self::redirect_with_notice( 'error', __( 'Sélectionnez au moins un utilisateur.', 'club-easy-event' ) );
                }

                $nonce = isset( $_POST['cee_roles_bulk_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['cee_roles_bulk_nonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Sanitized here.
                if ( ! wp_verify_nonce( $nonce, 'cee_roles_bulk' ) ) {
                        self::redirect_with_notice( 'error', __( 'La requête groupée a expiré. Veuillez réessayer.', 'club-easy-event' ) );
                }

                $action = isset( $_POST['cee_roles_bulk_action'] ) ? sanitize_text_field( wp_unslash( $_POST['cee_roles_bulk_action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Sanitized here.
                if ( ! in_array( $action, array( 'assign', 'remove' ), true ) ) {
                        self::redirect_with_notice( 'error', __( 'Veuillez sélectionner une action groupée valide.', 'club-easy-event' ) );
                }

                $roles = isset( $_POST['cee_roles_bulk_roles'] ) ? (array) wp_unslash( $_POST['cee_roles_bulk_roles'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Sanitized below.
                $roles = self::sanitize_role_list( $roles );

                if ( empty( $roles ) ) {
                        self::redirect_with_notice( 'error', __( 'Sélectionnez au moins un rôle à appliquer.', 'club-easy-event' ) );
                }

                $user_ids = array_map( 'absint', wp_unslash( $_POST['user_ids'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Sanitized here.
                $updated  = 0;

                foreach ( $user_ids as $user_id ) {
                        if ( ! $user_id || ! current_user_can( 'edit_user', $user_id ) ) {
                                continue;
                        }

                        $user = get_user_by( 'id', $user_id );
                        if ( ! $user ) {
                                continue;
                        }

                        foreach ( $roles as $role ) {
                                if ( 'assign' === $action ) {
                                        $user->add_role( $role );
                                } else {
                                        $user->remove_role( $role );
                                }
                        }
                        $updated++;
                }

                if ( $updated ) {
                        self::redirect_with_notice( 'updated', sprintf( _n( '%d utilisateur mis à jour.', '%d utilisateurs mis à jour.', $updated, 'club-easy-event' ), $updated ) );
                }

                self::redirect_with_notice( 'notice-warning', __( 'Aucun utilisateur n’a été modifié.', 'club-easy-event' ) );
        }

        /**
         * Assign roles to a user, replacing only managed roles.
         *
         * @param int   $user_id User ID.
         * @param array $roles   Roles to keep.
         *
         * @return void
         */
        protected static function assign_roles_to_user( $user_id, $roles ) {
                $roles         = self::sanitize_role_list( $roles );
                $allowed_roles = self::get_allowed_roles();

                $user = get_user_by( 'id', $user_id );
                if ( ! $user ) {
                        return;
                }

                foreach ( $allowed_roles as $role ) {
                        $user->remove_role( $role );
                }

                foreach ( $roles as $role ) {
                        if ( in_array( $role, $allowed_roles, true ) ) {
                                $user->add_role( $role );
                        }
                }
        }

        /**
         * Sanitize a list of roles.
         *
         * @param array $roles Raw roles.
         *
         * @return array
         */
        protected static function sanitize_role_list( $roles ) {
                $roles = array_filter( array_map( 'sanitize_key', $roles ) );
                return array_values( array_intersect( $roles, self::get_allowed_roles() ) );
        }

        /**
         * Retrieve allowed roles for the manager.
         *
         * @return array
         */
        protected static function get_allowed_roles() {
                $editable_roles = get_editable_roles();
                $roles          = array_keys( $editable_roles );

                if ( ! in_array( 'team_manager', $roles, true ) && get_role( 'team_manager' ) ) {
                        $roles[] = 'team_manager';
                }

                $roles = apply_filters( 'cee_roles_manager_allowed_roles', $roles );
                sort( $roles );

                return $roles;
        }

        /**
         * Map role slugs to labels.
         *
         * @param array $roles Roles.
         *
         * @return array
         */
        protected static function get_role_names( $roles ) {
                $editable_roles = get_editable_roles();
                $role_names     = array();

                foreach ( $roles as $role ) {
                        if ( isset( $editable_roles[ $role ]['name'] ) ) {
                                $role_names[ $role ] = translate_user_role( $editable_roles[ $role ]['name'] );
                        } elseif ( 'team_manager' === $role ) {
                                $role_names[ $role ] = __( 'Responsable d’équipe', 'club-easy-event' );
                        } else {
                                $role_names[ $role ] = $role;
                        }
                }

                return $role_names;
        }

        /**
         * Redirect back to the manager with a notice.
         *
         * @param string $type    Notice type (updated, error, notice-warning).
         * @param string $message Message.
         *
         * @return void
         */
        protected static function redirect_with_notice( $type, $message ) {
                set_transient(
                        'cee_roles_manager_notice',
                        array(
                                'type'    => $type,
                                'message' => $message,
                        ),
                        MINUTE_IN_SECONDS
                );

                $redirect = add_query_arg( array( 'page' => 'cee_roles' ), admin_url( 'admin.php' ) );

                wp_safe_redirect( $redirect );
                exit;
        }
}
