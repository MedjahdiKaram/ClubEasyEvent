<?php
/**
 * Cron events manager.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles scheduling of automated tasks.
 */
class CEE_Cron {

	/**
	 * Schedule daily reminder event on activation.
	 *
	 * @return void
	 */
	public static function activate_schedule() {
		if ( ! wp_next_scheduled( 'cee_daily_reminders' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'cee_daily_reminders' );
		}
	}

	/**
	 * Clear scheduled hook.
	 *
	 * @return void
	 */
	public static function deactivate_schedule() {
		$timestamp = wp_next_scheduled( 'cee_daily_reminders' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'cee_daily_reminders' );
		}
	}

	/**
	 * Send reminders for upcoming events.
	 *
	 * @return void
	 */
	public function send_daily_reminders() {
		$now      = current_time( 'timestamp' );
		$interval = DAY_IN_SECONDS;
		$end      = $now + $interval;

		$args = array(
			'post_type'      => 'cee_event',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'fields'         => 'ids',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_key'       => '_cee_event_date',
			'meta_query'     => array(
				array(
					'key'     => '_cee_event_date',
					'value'   => array( gmdate( 'Y-m-d', $now ), gmdate( 'Y-m-d', $end ) ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE',
				),
			),
		);

		$query = new WP_Query( $args );
		if ( empty( $query->posts ) ) {
			return;
		}

		$settings = new CEE_Settings();
		$template = $settings->get_email_template();

		foreach ( $query->posts as $event_id ) {
			$event_date = get_post_meta( $event_id, '_cee_event_date', true );
			$event_time = get_post_meta( $event_id, '_cee_event_time', true );
			$timestamp  = $this->get_event_timestamp( $event_date, $event_time );

			if ( ! $timestamp || $timestamp < $now || $timestamp > $end ) {
				continue;
			}

			$home_team_id = absint( get_post_meta( $event_id, '_cee_home_team_id', true ) );
			$away_team    = get_post_meta( $event_id, '_cee_away_team_id', true );
			$team_ids     = array();
			if ( $home_team_id ) {
				$team_ids[] = $home_team_id;
			}
			if ( $away_team && is_numeric( $away_team ) ) {
				$team_ids[] = absint( $away_team );
			}

			if ( empty( $team_ids ) ) {
				continue;
			}

			$recipients = $this->collect_recipients( $team_ids );
			$recipients = apply_filters( 'cee_email_recipients', $recipients, $event_id );

			if ( empty( $recipients ) || ! $template ) {
				continue;
			}

			$team_name = get_the_title( $home_team_id );
			$venue_id  = absint( get_post_meta( $event_id, '_cee_venue_id', true ) );
			$venue     = '';
			if ( $venue_id ) {
				$venue_title = get_the_title( $venue_id );
				$address     = get_post_meta( $venue_id, '_cee_venue_address', true );
				$venue       = trim( $venue_title . ' ' . $address );
			}

			foreach ( $recipients as $recipient ) {
				$user = get_user_by( 'email', $recipient );
				if ( ! $user ) {
					$user_id = email_exists( $recipient );
					if ( $user_id ) {
						$user = get_user_by( 'id', $user_id );
					}
				}
				$user_name = $user ? $user->display_name : $recipient;
				$message   = strtr(
					$template,
					array(
						'{event_name}' => get_the_title( $event_id ),
						'{event_date}' => date_i18n( get_option( 'date_format' ), $timestamp ),
						'{event_time}' => $event_time ? $event_time : '',
						'{event_link}' => get_permalink( $event_id ),
						'{team_name}'  => $team_name ? $team_name : '',
						'{venue}'      => $venue,
						'{user_name}'  => $user_name,
					)
				);

				$subject = sprintf( __( 'Rappel: %s', 'club-easy-event' ), get_the_title( $event_id ) );
				wp_mail( $recipient, $subject, $message );
			}
		}
	}

	/**
	 * Collect email recipients from team players.
	 *
	 * @param array $team_ids Team IDs.
	 *
	 * @return array
	 */
	private function collect_recipients( array $team_ids ) {
		$emails = array();
		foreach ( $team_ids as $team_id ) {
			$player_ids = (array) get_post_meta( $team_id, '_cee_team_players', true );
			foreach ( $player_ids as $player_id ) {
				$player_id = absint( $player_id );
				if ( ! $player_id ) {
					continue;
				}
				$user_id = absint( get_post_meta( $player_id, '_cee_player_user_id', true ) );
				if ( ! $user_id ) {
					continue;
				}
				$user = get_userdata( $user_id );
				if ( $user && $user->user_email ) {
					$emails[] = sanitize_email( $user->user_email );
				}
			}
		}

		return array_unique( array_filter( $emails ) );
	}

	/**
	 * Combine date and time into timestamp.
	 *
	 * @param string $date Date string.
	 * @param string $time Time string.
	 *
	 * @return int|null
	 */
	private function get_event_timestamp( $date, $time ) {
		if ( ! $date ) {
			return null;
		}
		$datetime = $date;
		if ( $time ) {
			$datetime .= ' ' . $time;
		}
		$timestamp = strtotime( $datetime );
		return $timestamp ? $timestamp : null;
	}
}
