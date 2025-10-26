<?php
/**
 * Admin column handlers.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles custom admin columns for events.
 */
class CEE_Admin_Columns {

	/**
	 * Modify event columns.
	 *
	 * @param array $columns Existing columns.
	 *
	 * @return array
	 */
	public function add_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new_columns['title'] = __( 'Titre', 'club-easy-event' );
				$new_columns['cee_date'] = __( 'Date', 'club-easy-event' );
				$new_columns['cee_time'] = __( 'Heure', 'club-easy-event' );
				$new_columns['cee_teams'] = __( 'Équipes', 'club-easy-event' );
				$new_columns['cee_season'] = __( 'Saison', 'club-easy-event' );
			} elseif ( 'date' === $key ) {
				$new_columns['date'] = $label;
			} else {
				$new_columns[ $key ] = $label;
			}
		}

		return $new_columns;
	}

	/**
	 * Render custom column values.
	 *
	 * @param string $column Column name.
	 * @param int    $post_id Post ID.
	 *
	 * @return void
	 */
	public function render_column( $column, $post_id ) {
		switch ( $column ) {
			case 'cee_date':
				$event_date = get_post_meta( $post_id, '_cee_event_date', true );
				if ( $event_date ) {
					echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $event_date ) ) );
				}
				break;
			case 'cee_time':
				$event_time = get_post_meta( $post_id, '_cee_event_time', true );
				if ( $event_time ) {
					echo esc_html( $event_time );
				}
				break;
			case 'cee_teams':
				$home_team = absint( get_post_meta( $post_id, '_cee_home_team_id', true ) );
				$away_team = get_post_meta( $post_id, '_cee_away_team_id', true );
				$teams     = array();
				if ( $home_team ) {
					$teams[] = get_the_title( $home_team );
				}
				if ( $away_team ) {
					if ( is_numeric( $away_team ) ) {
						$teams[] = get_the_title( absint( $away_team ) );
					} else {
						$teams[] = $away_team;
					}
				}
				$separator = ' ' . esc_html__( 'vs', 'club-easy-event' ) . ' ';
				echo esc_html( implode( $separator, array_filter( $teams ) ) );
				break;
			case 'cee_season':
				$terms = get_the_terms( $post_id, 'cee_season' );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$names = wp_list_pluck( $terms, 'name' );
					echo esc_html( implode( ', ', $names ) );
				}
				break;
		}
	}

	/**
	 * Register sortable columns.
	 *
	 * @param array $columns Columns.
	 *
	 * @return array
	 */
	public function register_sortable_columns( $columns ) {
		$columns['cee_date'] = 'cee_date';
		return $columns;
	}

	/**
	 * Handle sorting for custom columns.
	 *
	 * @param WP_Query $query Query instance.
	 *
	 * @return void
	 */
	public function handle_sorting( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'cee_event' !== $query->get( 'post_type' ) ) {
			return;
		}

		$orderby = $query->get( 'orderby' );
		if ( 'cee_date' === $orderby ) {
			$query->set( 'meta_key', '_cee_event_date' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_type', 'DATE' );
		}
	}
}
