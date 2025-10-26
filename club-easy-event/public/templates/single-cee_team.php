<?php
/**
 * Template for single team posts.
 *
 * @package ClubEasyEvent
 */

get_header();
?>
<main id="primary" class="cee-single-team">
	<?php while ( have_posts() ) : the_post();
		$team_id    = get_the_ID();
		$player_ids = (array) get_post_meta( $team_id, '_cee_team_players', true );
		$upcoming   = new WP_Query(
			array(
				'post_type'      => 'cee_event',
				'post_status'    => 'publish',
				'posts_per_page' => 5,
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_key'       => '_cee_event_date',
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'   => '_cee_home_team_id',
						'value' => $team_id,
						'compare' => '=',
					),
					array(
						'key'   => '_cee_away_team_id',
						'value' => $team_id,
						'compare' => '=',
					),
				),
			)
		);
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'cee-team-article' ); ?>>
			<header class="entry-header">
				<h1 class="entry-title"><?php the_title(); ?></h1>
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="cee-team-thumbnail"><?php the_post_thumbnail( 'large' ); ?></div>
				<?php endif; ?>
			</header>
			<div class="entry-content"><?php the_content(); ?></div>

			<?php if ( ! empty( $player_ids ) ) :
				$shortcode = sprintf( '[cee_roster team_id="%d"]', $team_id );
				?>
				<section class="cee-team-roster">
					<h2><?php esc_html_e( 'Effectif', 'club-easy-event' ); ?></h2>
					<?php echo do_shortcode( $shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</section>
			<?php endif; ?>

			<?php if ( $upcoming->have_posts() ) : ?>
				<section class="cee-team-upcoming">
					<h2><?php esc_html_e( 'Prochains événements', 'club-easy-event' ); ?></h2>
					<ul>
						<?php while ( $upcoming->have_posts() ) : $upcoming->the_post(); ?>
							<li><a href="<?php echo esc_url( get_permalink() ); ?>"><?php the_title(); ?></a> — <?php echo esc_html( get_post_meta( get_the_ID(), '_cee_event_date', true ) ); ?></li>
						<?php endwhile; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php wp_reset_postdata(); ?>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();