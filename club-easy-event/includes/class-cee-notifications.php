<?php
/**
 * Notification helpers.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

/**
 * Sends update notifications when events change.
 */
class CEE_Notifications {

        const THROTTLE_MINUTES = 30;

        /**
         * Settings manager.
         *
         * @var CEE_Settings
         */
        protected $settings;

        /**
         * Cached snapshots.
         *
         * @var array
         */
        protected $snapshots = array();

        /**
         * Constructor.
         *
         * @param CEE_Settings $settings Settings manager.
         */
        public function __construct( CEE_Settings $settings ) {
                $this->settings = $settings;
        }

        /**
         * Register admin hooks.
         *
         * @param CEE_Loader $loader Loader instance.
         *
         * @return void
         */
        public function register_admin_hooks( CEE_Loader $loader ) {
                $loader->add_action( 'pre_post_update', $this, 'capture_snapshot', 10, 2 );
                $loader->add_action( 'save_post_cee_event', $this, 'maybe_send_notifications', 20, 3 );
        }

        /**
         * Register public hooks.
         *
         * @param CEE_Loader $loader Loader instance.
         *
         * @return void
         */
        public function register_public_hooks( CEE_Loader $loader ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
                // Reserved for future front-end hooks.
        }

        /**
         * Capture snapshot before update.
         *
         * @param int   $post_id Post ID.
         * @param array $data    Post data.
         *
         * @return void
         */
        public function capture_snapshot( $post_id, $data ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
                if ( 'cee_event' !== get_post_type( $post_id ) ) {
                        return;
                }

                $this->snapshots[ $post_id ] = $this->get_event_snapshot( $post_id );
        }

        /**
         * Maybe send notifications after event update.
         *
         * @param int     $post_id Post ID.
         * @param WP_Post $post    Post object.
         * @param bool    $update  Whether this is an update.
         *
         * @return void
         */
        public function maybe_send_notifications( $post_id, $post, $update ) {
                if ( ! $update || 'cee_event' !== $post->post_type ) {
                        return;
                }

                if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                        return;
                }

                if ( wp_is_post_revision( $post_id ) ) {
                        return;
                }

                $settings = $this->settings->get_settings();
                $enabled  = isset( $settings['notify_on_event_update'] ) && '1' === $settings['notify_on_event_update'];
                if ( ! $enabled ) {
                        return;
                }

                $previous = isset( $this->snapshots[ $post_id ] ) ? $this->snapshots[ $post_id ] : $this->get_event_snapshot( $post_id );
                $current  = $this->get_event_snapshot( $post_id );

                $diff = $this->diff_snapshots( $previous, $current );
                if ( empty( $diff ) ) {
                        return;
                }

                $this->send_event_update_notifications( $post_id, $diff );
                unset( $this->snapshots[ $post_id ] );
        }

        /**
         * Send notifications.
         *
         * @param int   $event_id Event ID.
         * @param array $diff     Changes.
         *
         * @return void
         */
        public function send_event_update_notifications( $event_id, array $diff ) {
                $recipients = $this->get_recipients_for_event( $event_id );
                if ( empty( $recipients ) ) {
                        return;
                }

                list( $subject, $body ) = $this->build_email_from_template( $event_id, $diff );
                if ( ! $subject || ! $body ) {
                        return;
                }

                foreach ( $recipients as $email ) {
                        if ( ! is_email( $email ) ) {
                                continue;
                        }

                        $throttle_key = 'cee_event_notice_' . md5( $event_id . '|' . $email );
                        if ( get_transient( $throttle_key ) ) {
                                continue;
                        }

                        wp_mail( $email, $subject, $body );
                        set_transient( $throttle_key, 1, self::THROTTLE_MINUTES * MINUTE_IN_SECONDS );
                }
        }

        /**
         * Build email content from template.
         *
         * @param int   $event_id Event ID.
         * @param array $diff     Diff array.
         *
         * @return array Array with subject and body.
         */
        public function build_email_from_template( $event_id, array $diff ) {
                $event      = get_post( $event_id );
                $event_name = $event ? get_the_title( $event ) : '';
                $date       = get_post_meta( $event_id, '_cee_event_date', true );
                $time       = get_post_meta( $event_id, '_cee_event_time', true );
                $home_team  = get_post_meta( $event_id, '_cee_home_team_id', true );
                $away_team  = get_post_meta( $event_id, '_cee_away_team_id', true );
                $venue_id   = get_post_meta( $event_id, '_cee_venue_id', true );

                $home_team_name = $home_team ? get_the_title( absint( $home_team ) ) : '';
                $away_team_name = '';
                if ( $away_team ) {
                        $away_team_name = is_numeric( $away_team ) ? get_the_title( absint( $away_team ) ) : $away_team;
                }

                $venue_name = $venue_id ? get_the_title( $venue_id ) : '';

                $changes_list = '';
                if ( ! empty( $diff ) ) {
                        $changes = array();
                        foreach ( $diff as $label => $change ) {
                                $changes[] = sprintf( '%1$s: %2$s → %3$s', $label, $change['from'], $change['to'] );
                        }
                        $changes_list = implode( "\n", $changes );
                }

                $tags = array(
                        '{site_name}' => wp_specialchars_decode( get_bloginfo( 'name' ) ),
                        '{event_name}'  => $event_name,
                        '{event_date}'  => $date ? date_i18n( get_option( 'date_format' ), strtotime( $date ) ) : '',
                        '{event_time}'  => $time,
                        '{team_home}'   => $home_team_name,
                        '{team_away}'   => $away_team_name,
                        '{venue}'       => $venue_name,
                        '{event_link}'  => get_permalink( $event_id ),
                        '{changes_list}' => $changes_list,
                );

                $subject = sprintf( __( '[%1$s] Mise à jour de l’événement: %2$s', 'club-easy-event' ), wp_specialchars_decode( get_bloginfo( 'name' ) ), $event_name );
                $body    = __( "Bonjour,\n\nDes modifications ont été appliquées à l’événement {event_name}.", 'club-easy-event' ) . "\n\n{changes_list}\n\n" . __( 'Voir les détails complets: {event_link}', 'club-easy-event' );

                $settings = $this->settings->get_settings();
                if ( ! empty( $settings['event_update_subject'] ) ) {
                        $subject = $settings['event_update_subject'];
                }
                if ( ! empty( $settings['event_update_template'] ) ) {
                        $body = $settings['event_update_template'];
                }

                $subject = strtr( $subject, $tags );
                $body    = strtr( $body, $tags );

                /**
                 * Filter the event notification template output.
                 *
                 * @param string $subject Email subject.
                 * @param string $body    Email body.
                 * @param int    $event_id Event ID.
                 * @param array  $diff     Diff array.
                 */
                $filtered = apply_filters( 'cee_event_update_template', array( $subject, $body ), $event_id, $diff );
                if ( is_array( $filtered ) && 2 === count( $filtered ) ) {
                        $subject = $filtered[0];
                        $body    = $filtered[1];
                }

                return array( $subject, $body );
        }

        /**
         * Retrieve recipients for event.
         *
         * @param int $event_id Event ID.
         *
         * @return array
         */
        public function get_recipients_for_event( $event_id ) {
                $emails   = array();
                $home     = absint( get_post_meta( $event_id, '_cee_home_team_id', true ) );
                $away_raw = get_post_meta( $event_id, '_cee_away_team_id', true );
                $team_ids = array();
                if ( $home ) {
                        $team_ids[] = $home;
                }
                if ( $away_raw && is_numeric( $away_raw ) ) {
                        $team_ids[] = absint( $away_raw );
                }

                foreach ( $team_ids as $team_id ) {
                        $players = (array) get_post_meta( $team_id, '_cee_team_players', true );
                        foreach ( $players as $player_id ) {
                                $user_id = absint( get_post_meta( $player_id, '_cee_player_user_id', true ) );
                                if ( $user_id ) {
                                        $user = get_user_by( 'id', $user_id );
                                        if ( $user && $user->user_email ) {
                                                $emails[] = $user->user_email;
                                        }
                                }
                        }
                }

                $manager_users = get_users( array( 'role' => 'team_manager', 'fields' => array( 'user_email' ) ) );
                foreach ( $manager_users as $user ) {
                        if ( ! empty( $user->user_email ) ) {
                                $emails[] = $user->user_email;
                        }
                }

                $emails = array_unique( array_filter( $emails ) );

                /**
                 * Filter recipients for event updates.
                 *
                 * @param array $emails   Email list.
                 * @param int   $event_id Event ID.
                 */
                $emails = apply_filters( 'cee_event_update_recipients', $emails, $event_id );

                return $emails;
        }

        /**
         * Build snapshot of event meta.
         *
         * @param int $event_id Event ID.
         *
         * @return array
         */
        protected function get_event_snapshot( $event_id ) {
                return array(
                        'event_date' => get_post_meta( $event_id, '_cee_event_date', true ),
                        'event_time' => get_post_meta( $event_id, '_cee_event_time', true ),
                        'venue_id'   => get_post_meta( $event_id, '_cee_venue_id', true ),
                        'home_team'  => get_post_meta( $event_id, '_cee_home_team_id', true ),
                        'away_team'  => get_post_meta( $event_id, '_cee_away_team_id', true ),
                );
        }

        /**
         * Generate diff array.
         *
         * @param array $previous Previous snapshot.
         * @param array $current  Current snapshot.
         *
         * @return array
         */
        protected function diff_snapshots( array $previous, array $current ) {
                $fields = array(
                        'event_date' => __( 'Date', 'club-easy-event' ),
                        'event_time' => __( 'Heure', 'club-easy-event' ),
                        'venue_id'   => __( 'Lieu', 'club-easy-event' ),
                        'home_team'  => __( 'Équipe à domicile', 'club-easy-event' ),
                        'away_team'  => __( 'Équipe adverse', 'club-easy-event' ),
                );

                $diff = array();
                foreach ( $fields as $key => $label ) {
                        $old = isset( $previous[ $key ] ) ? $previous[ $key ] : '';
                        $new = isset( $current[ $key ] ) ? $current[ $key ] : '';

                        if ( $old === $new ) {
                                continue;
                        }

                        $diff[ $label ] = array(
                                'from' => $this->format_snapshot_value( $key, $old ),
                                'to'   => $this->format_snapshot_value( $key, $new ),
                        );
                }

                return $diff;
        }

        /**
         * Format snapshot values for output.
         *
         * @param string $key Field key.
         * @param mixed  $value Value.
         *
         * @return string
         */
        protected function format_snapshot_value( $key, $value ) {
                if ( '' === $value ) {
                        return __( 'Non défini', 'club-easy-event' );
                }

                switch ( $key ) {
                        case 'event_date':
                                return date_i18n( get_option( 'date_format' ), strtotime( $value ) );
                        case 'event_time':
                                return $value;
                        case 'venue_id':
                                return is_numeric( $value ) ? get_the_title( absint( $value ) ) : $value;
                        case 'home_team':
                                return is_numeric( $value ) ? get_the_title( absint( $value ) ) : $value;
                        case 'away_team':
                                if ( is_numeric( $value ) ) {
                                        return get_the_title( absint( $value ) );
                                }
                                return $value;
                        default:
                                return (string) $value;
                }
        }
}
