<?php
/**
 * Template for single event posts.
 *
 * @package ClubEasyEvent
 */

get_header();
?>
<main id="primary" class="cee-single-event">
	<?php while ( have_posts() ) : the_post();
		$event_id     = get_the_ID();
		$event_date   = get_post_meta( $event_id, '_cee_event_date', true );
		$event_time   = get_post_meta( $event_id, '_cee_event_time', true );
		$event_type   = get_post_meta( $event_id, '_cee_event_type', true );
		$home_team_id = absint( get_post_meta( $event_id, '_cee_home_team_id', true ) );
		$away_team    = get_post_meta( $event_id, '_cee_away_team_id', true );
		$venue_id     = absint( get_post_meta( $event_id, '_cee_venue_id', true ) );
		$home_score   = get_post_meta( $event_id, '_cee_home_score', true );
		$away_score   = get_post_meta( $event_id, '_cee_away_score', true );
		$rsvp_data    = get_post_meta( $event_id, '_cee_rsvp_data', true );
		$rsvp_data    = is_array( $rsvp_data ) ? $rsvp_data : array();
		$confirmed    = array();
		foreach ( $rsvp_data as $user_id => $status ) {
			if ( 'yes' !== $status ) {
				continue;
			}
			$user = get_userdata( $user_id );
			if ( $user ) {
				$confirmed[] = $user->display_name;
			}
		}
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'cee-event-article' ); ?>>
			<header class="entry-header">
				<h1 class="entry-title"><?php the_title(); ?></h1>
				<?php if ( $event_type ) : ?>
					<p class="cee-event-type-label"><?php echo esc_html( $event_type ); ?></p>
				<?php endif; ?>
			</header>
			<div class="cee-event-meta">
				<?php if ( $event_date ) : ?>
					<p><strong><?php esc_html_e( 'Date', 'club-easy-event' ); ?>:</strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $event_date ) ) ); ?></p>
				<?php endif; ?>
				<?php if ( $event_time ) : ?>
					<p><strong><?php esc_html_e( 'Heure', 'club-easy-event' ); ?>:</strong> <?php echo esc_html( $event_time ); ?></p>
				<?php endif; ?>
				<?php
				$home_team_name = $home_team_id ? get_the_title( $home_team_id ) : '';
				$away_team_name = '';
				if ( $away_team ) {
					$away_team_name = is_numeric( $away_team ) ? get_the_title( absint( $away_team ) ) : $away_team;
				}
				?>
				<?php if ( $home_team_name || $away_team_name ) : ?>
					<p><strong><?php esc_html_e( 'Affiche', 'club-easy-event' ); ?>:</strong> <?php echo esc_html( trim( $home_team_name . ( $away_team_name ? ' vs ' . $away_team_name : '' ) ) ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $home_score || '' !== $away_score ) : ?>
					<p><strong><?php esc_html_e( 'Score', 'club-easy-event' ); ?>:</strong> <?php echo esc_html( $home_score . ' - ' . $away_score ); ?></p>
				<?php endif; ?>
				<?php if ( $venue_id ) :
					$venue_title = get_the_title( $venue_id );
					$venue_addr  = get_post_meta( $venue_id, '_cee_venue_address', true );
					$venue_link  = get_post_meta( $venue_id, '_cee_venue_map_link', true );
					?>
					<p><strong><?php esc_html_e( 'Lieu', 'club-easy-event' ); ?>:</strong> <?php echo esc_html( $venue_title ); ?><?php if ( $venue_addr ) : ?> — <?php echo esc_html( $venue_addr ); ?><?php endif; ?><?php if ( $venue_link ) : ?> — <a href="<?php echo esc_url( $venue_link ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Voir sur la carte', 'club-easy-event' ); ?></a><?php endif; ?></p>
				<?php endif; ?>
			</div>

			<div class="entry-content">
				<?php the_content(); ?>
			</div>

			<?php if ( ! empty( $confirmed ) ) : ?>
				<section class="cee-event-rsvp-confirmed">
					<h2><?php esc_html_e( 'Joueurs présents', 'club-easy-event' ); ?></h2>
					<ul>
						<?php foreach ( $confirmed as $name ) : ?>
							<li><?php echo esc_html( $name ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();