<?php
/**
 * Admin onboarding helper.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

/**
 * Handles the display and dismissal of the admin onboarding.
 */
class CEE_Onboarding {

        /**
         * User meta key storing dismissal flag.
         */
        const META_KEY = 'cee_onboarding_dismissed';

        /**
         * Determine if the current admin screen belongs to the plugin.
         *
         * @return bool
         */
        public static function is_plugin_screen() {
                if ( ! is_admin() ) {
                        return false;
                }

                $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
                if ( ! $screen ) {
                        return false;
                }

                $screen_id = isset( $screen->id ) ? $screen->id : '';
                $post_type = isset( $screen->post_type ) ? $screen->post_type : '';
                $taxonomy  = isset( $screen->taxonomy ) ? $screen->taxonomy : '';

                if ( in_array( $screen_id, array( 'toplevel_page_cee_dashboard', 'club-easy-event_page_cee_settings' ), true ) ) {
                        return true;
                }

                if ( $post_type && 0 === strpos( $post_type, 'cee_' ) ) {
                        return true;
                }

                if ( $taxonomy && 0 === strpos( $taxonomy, 'cee_' ) ) {
                        return true;
                }

                return false;
        }

        /**
         * Check if the current user dismissed the onboarding.
         *
         * @return bool
         */
        public static function user_dismissed() {
                $user_id = get_current_user_id();
                if ( ! $user_id ) {
                        return false;
                }

                return (bool) get_user_meta( $user_id, self::META_KEY, true );
        }

        /**
         * Determine if the onboarding should be displayed.
         *
         * @return bool
         */
        public static function should_display() {
                if ( ! current_user_can( 'read' ) ) {
                        return false;
                }

                if ( self::user_dismissed() ) {
                        return false;
                }

                return self::is_plugin_screen();
        }

        /**
         * Render onboarding markup when appropriate.
         *
         * @return void
         */
        public static function maybe_render() {
                if ( ! self::should_display() ) {
                        return;
                }

                $template = CEE_PLUGIN_DIR . 'admin/views/onboarding.php';
                if ( file_exists( $template ) ) {
                        include $template;
                }
        }

        /**
         * Handle AJAX dismissal of onboarding.
         *
         * @return void
         */
        public static function ajax_dismiss() {
                check_ajax_referer( 'cee_onboarding_nonce', 'nonce' );

                if ( ! current_user_can( 'read' ) ) {
                        wp_send_json_error();
                }

                $user_id = get_current_user_id();
                if ( ! $user_id ) {
                        wp_send_json_error();
                }

                update_user_meta( $user_id, self::META_KEY, '1' );

                wp_send_json_success();
        }
}
