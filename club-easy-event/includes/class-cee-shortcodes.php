<?php
/**
 * Shortcode handlers.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides shortcodes for schedules and rosters.
 */
class CEE_Shortcodes {

	/**
	 * Front-end manager.
	 *
	 * @var CEE_Frontend
	 */
	protected $frontend;

	/**
	 * WooCommerce helper.
	 *
	 * @var CEE_WooCommerce
	 */
	protected $woocommerce;

	/**
	 * Constructor.
	 *
	 * @param CEE_Frontend    $frontend    Front-end manager.
	 * @param CEE_WooCommerce $woocommerce WooCommerce helper.
	 */
	public function __construct( CEE_Frontend $frontend, CEE_WooCommerce $woocommerce ) {
		$this->frontend    = $frontend;
		$this->woocommerce = $woocommerce;
	}

	/**
	 * Register shortcodes.
	 *
	 * @return void
	 */
	public function register_shortcodes() {
		add_shortcode( 'cee_schedule', array( $this, 'render_schedule' ) );
		add_shortcode( 'cee_roster', array( $this, 'render_roster' ) );
	}

	/**
	 * Render schedule shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function render_schedule( $atts ) {
		$atts = shortcode_atts(
			array(
				'team_id'   => '',
				'season_id' => '',
			),
			$atts,
			'cee_schedule'
		);

		$team_id   = absint( $atts['team_id'] );
		$season_id = absint( $atts['season_id'] );

		if ( ! $team_id ) {
			return '';
		}

		$this->frontend->mark_assets_needed();

		$meta_query = array(
			'relation' => 'OR',
			array(
				'key'   => '_cee_home_team_id',
				'value' => $team_id,
				'compare' => '=',
			),
			array(
				'key'     => '_cee_away_team_id',
				'value'   => $team_id,
				'compare' => '=',
			),
		);

		$tax_query = array();
		if ( $season_id ) {
			$tax_query[] = array(
				'taxonomy' => 'cee_season',
				'field'    => 'term_id',
				'terms'    => array( $season_id ),
			);
		}

		$args = array(
			'post_type'      => 'cee_event',
			'post_status'    => 'publish',
			'posts_per_page' => 30,
			'meta_query'     => $meta_query,
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_key'       => '_cee_event_date',
			'tax_query'      => $tax_query,
		);

		$args = apply_filters( 'cee_schedule_query_args', $args, $atts );

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<div class="cee-schedule-wrapper"><p>' . esc_html__( 'Aucun événement à afficher.', 'club-easy-event' ) . '</p></div>';
		}

		$current_user_id = get_current_user_id();
		$is_member       = $current_user_id && $this->user_is_team_member( $current_user_id, $team_id );
		$nonce           = wp_create_nonce( 'cee_rsvp_nonce' );
		$now             = current_time( 'timestamp' );
		$team_name       = get_the_title( $team_id );

		ob_start();
		?>
		<div class="cee-schedule-wrapper" data-team-id="<?php echo esc_attr( $team_id ); ?>">
		<h3 class="cee-schedule-title"><?php echo esc_html( $team_name ); ?></h3>
		<ul class="cee-schedule-list">
		<?php
		while ( $query->have_posts() ) :
		$query->the_post();
		$event_id    = get_the_ID();
		$event_date  = get_post_meta( $event_id, '_cee_event_date', true );
		$event_time  = get_post_meta( $event_id, '_cee_event_time', true );
		$event_type  = get_post_meta( $event_id, '_cee_event_type', true );
		$home_team   = absint( get_post_meta( $event_id, '_cee_home_team_id', true ) );
		$away_team   = get_post_meta( $event_id, '_cee_away_team_id', true );
		$venue_id    = absint( get_post_meta( $event_id, '_cee_venue_id', true ) );
		$home_score  = get_post_meta( $event_id, '_cee_home_score', true );
		$away_score  = get_post_meta( $event_id, '_cee_away_score', true );
		$event_time_stamp = $this->get_event_timestamp( $event_date, $event_time );
		$is_past     = $event_time_stamp && $event_time_stamp < $now;
		$rsvp_data   = get_post_meta( $event_id, '_cee_rsvp_data', true );
		$rsvp_data   = is_array( $rsvp_data ) ? $rsvp_data : array();
		$user_rsvp   = $current_user_id && isset( $rsvp_data[ $current_user_id ] ) ? $rsvp_data[ $current_user_id ] : '';
		$away_team_name = '';
		if ( $away_team ) {
		$away_team_name = is_numeric( $away_team ) ? get_the_title( absint( $away_team ) ) : $away_team;
		}
		?>
		<li class="cee-schedule-item">
		<div class="cee-schedule-header">
		<a class="cee-schedule-event-title" href="<?php echo esc_url( get_permalink() ); ?>"><?php the_title(); ?></a>
		<?php if ( $event_type ) : ?>
		<span class="cee-event-type"><?php echo esc_html( $event_type ); ?></span>
		<?php endif; ?>
		</div>
		<div class="cee-schedule-meta">
		<span class="cee-event-date"><?php echo esc_html( $this->format_date( $event_date ) ); ?></span>
		<?php if ( $event_time ) : ?>
		<span class="cee-event-time"><?php echo esc_html( $event_time ); ?></span>
		<?php endif; ?>
		</div>
		<div class="cee-event-matchup">
		<?php
		$home_name = $home_team ? get_the_title( $home_team ) : '';
		if ( $home_name ) {
		echo esc_html( $home_name );
		}
		if ( $away_team_name ) {
		echo ' ' . esc_html__( 'vs', 'club-easy-event' ) . ' ' . esc_html( $away_team_name );
		}
		?>
		</div>
		<?php if ( $venue_id ) : ?>
		<div class="cee-event-venue">
		<?php
		$venue_title = get_the_title( $venue_id );
		$address     = get_post_meta( $venue_id, '_cee_venue_address', true );
		$map_link    = get_post_meta( $venue_id, '_cee_venue_map_link', true );
		if ( $venue_title ) {
		echo esc_html( $venue_title );
		}
		if ( $address ) {
		echo ' — ' . esc_html( $address );
		}
		if ( $map_link ) {
		echo ' <a href="' . esc_url( $map_link ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Itinéraire', 'club-easy-event' ) . '</a>';
		}
		?>
		</div>
		<?php endif; ?>
		<?php if ( $is_past ) : ?>
		<?php if ( '' !== $home_score || '' !== $away_score ) : ?>
		<div class="cee-event-score">
		<?php echo esc_html( $home_score . ' - ' . $away_score ); ?>
		</div>
		<?php endif; ?>
		<?php elseif ( $is_member ) : ?>
		<div class="cee-event-rsvp" data-event-id="<?php echo esc_attr( $event_id ); ?>">
		<?php foreach ( array( 'yes' => __( 'Présent', 'club-easy-event' ), 'maybe' => __( 'Incertain', 'club-easy-event' ), 'no' => __( 'Absent', 'club-easy-event' ) ) as $value => $label ) : ?>
		<button type="button" class="button cee-rsvp-button<?php echo ( $user_rsvp === $value ) ? ' is-active' : ''; ?>" data-response="<?php echo esc_attr( $value ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>">
		<?php echo esc_html( $label ); ?>
		</button>
		<?php endforeach; ?>
		<span class="cee-rsvp-status" aria-live="polite"></span>
		</div>
		<?php endif; ?>
		</li>
		<?php endwhile; ?>
		</ul>
		</div>
		<?php
		wp_reset_postdata();
		return ob_get_clean();
	}

	/**
	 * Render roster shortcode.
	 *
	 * @param array $atts Attributes.
	 *
	 * @return string
	 */
	public function render_roster( $atts ) {
		$atts = shortcode_atts(
			array(
				'team_id'   => '',
				'season_id' => '',
			),
			$atts,
			'cee_roster'
		);

		$team_id = absint( $atts['team_id'] );
		if ( ! $team_id ) {
			return '';
		}

		$this->frontend->mark_assets_needed();

		$season_id = absint( $atts['season_id'] );
		if ( ! $season_id ) {
			$season_terms = wp_get_post_terms( $team_id, 'cee_season', array( 'fields' => 'ids' ) );
			if ( ! is_wp_error( $season_terms ) && ! empty( $season_terms ) ) {
				$season_id = absint( $season_terms[0] );
			}
		}

		$player_ids = (array) get_post_meta( $team_id, '_cee_team_players', true );
		$player_ids = array_filter( array_map( 'absint', $player_ids ) );

		if ( empty( $player_ids ) ) {
			return '<div class="cee-roster-wrapper"><p>' . esc_html__( 'Aucun joueur enregistré.', 'club-easy-event' ) . '</p></div>';
		}

		$players = get_posts(
			array(
				'post_type'      => 'cee_player',
				'post__in'       => $player_ids,
				'orderby'        => 'post__in',
				'posts_per_page' => count( $player_ids ),
			)
		);

		ob_start();
		?>
		<div class="cee-roster-wrapper">
		<ul class="cee-roster-list">
		<?php foreach ( $players as $player ) :
				$number   = get_post_meta( $player->ID, '_cee_player_number', true );
				$position = get_post_meta( $player->ID, '_cee_player_position', true );
				$user_id  = absint( get_post_meta( $player->ID, '_cee_player_user_id', true ) );
				$has_paid = false;
				if ( $season_id && $user_id ) {
				$has_paid = $this->woocommerce->has_user_paid_for_season( $user_id, $season_id );
				}
				?>
		<li class="cee-roster-player">
		<div class="cee-roster-photo">
		<?php
						$thumb = get_the_post_thumbnail( $player->ID, 'thumbnail', array( 'loading' => 'lazy', 'class' => 'cee-player-thumb' ) );
						echo $thumb ? $thumb : '<span class="cee-placeholder-avatar" aria-hidden="true">' . esc_html__( 'Aucun visuel', 'club-easy-event' ) . '</span>';
					?>
		</div>
		<div class="cee-roster-info">
		<strong class="cee-player-name"><?php echo esc_html( get_the_title( $player ) ); ?></strong>
		<?php if ( '' !== $number ) : ?>
		<span class="cee-player-number"><?php echo esc_html( '#' . $number ); ?></span>
		<?php endif; ?>
		<?php if ( $position ) : ?>
		<span class="cee-player-position"><?php echo esc_html( $position ); ?></span>
		<?php endif; ?>
		<?php if ( $has_paid ) : ?>
		<span class="cee-player-paid" title="<?php echo esc_attr__( 'Cotisation réglée', 'club-easy-event' ); ?>">✔</span>
		<?php endif; ?>
		</div>
		</li>
		<?php endforeach; ?>
		</ul>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Determine if a user is linked to a player on a team.
	 *
	 * @param int $user_id User ID.
	 * @param int $team_id Team ID.
	 *
	 * @return bool
	 */
	private function user_is_team_member( $user_id, $team_id ) {
		$player_ids = (array) get_post_meta( $team_id, '_cee_team_players', true );
		foreach ( $player_ids as $player_id ) {
			$player_id = absint( $player_id );
			if ( ! $player_id ) {
				continue;
			}
			$linked_user = absint( get_post_meta( $player_id, '_cee_player_user_id', true ) );
			if ( $linked_user && $linked_user === $user_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Format date.
	 *
	 * @param string $date Date string.
	 *
	 * @return string
	 */
	private function format_date( $date ) {
		if ( ! $date ) {
			return '';
		}

		$timestamp = strtotime( $date );
		if ( ! $timestamp ) {
			return $date;
		}

		return date_i18n( get_option( 'date_format' ), $timestamp );
	}

	/**
	 * Get event timestamp.
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
