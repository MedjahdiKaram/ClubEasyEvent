<?php
/**
 * Event meta box view.
 *
 * @var array $teams
 * @var array $venues
 * @var string $event_date
 * @var string $event_time
 * @var string $event_type
 * @var int    $home_team_id
 * @var mixed  $away_team_value
 * @var int    $venue_id
 * @var string $home_score
 * @var string $away_score
 */
?>
<?php wp_nonce_field( 'cee_event_meta', 'cee_event_meta_nonce' ); ?>
<p>
<label for="cee_event_date"><strong><?php esc_html_e( 'Date', 'club-easy-event' ); ?></strong></label>
<input type="text" id="cee_event_date" name="cee_event_date" class="cee-date-field regular-text" value="<?php echo esc_attr( $event_date ); ?>" placeholder="YYYY-MM-DD" />
</p>
<p>
<label for="cee_event_time"><strong><?php esc_html_e( 'Heure', 'club-easy-event' ); ?></strong></label>
<input type="text" id="cee_event_time" name="cee_event_time" class="cee-time-field regular-text" value="<?php echo esc_attr( $event_time ); ?>" placeholder="HH:MM" />
</p>
<p>
<label for="cee_event_type"><strong><?php esc_html_e( 'Type d’événement', 'club-easy-event' ); ?></strong></label>
<select id="cee_event_type" name="cee_event_type" class="widefat">
<?php foreach ( array( 'Match', 'Entraînement', 'Événement social' ) as $type ) : ?>
<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $event_type, $type ); ?>><?php echo esc_html( $type ); ?></option>
<?php endforeach; ?>
</select>
</p>
<p>
<label for="cee_home_team_id"><strong><?php esc_html_e( 'Équipe à domicile', 'club-easy-event' ); ?></strong></label>
<select id="cee_home_team_id" name="cee_home_team_id" class="widefat">
<option value="0"><?php esc_html_e( '— Sélectionner —', 'club-easy-event' ); ?></option>
<?php foreach ( $teams as $id => $label ) : ?>
<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $home_team_id, $id ); ?>><?php echo esc_html( $label ); ?></option>
<?php endforeach; ?>
</select>
</p>
<p>
<label for="cee_away_team_select"><strong><?php esc_html_e( 'Équipe adverse', 'club-easy-event' ); ?></strong></label>
<select id="cee_away_team_select" name="cee_away_team_id_select" class="widefat">
<option value=""><?php esc_html_e( '— Équipe externe (voir champ texte) —', 'club-easy-event' ); ?></option>
<?php foreach ( $teams as $id => $label ) : ?>
<option value="<?php echo esc_attr( $id ); ?>" <?php selected( (int) $away_team_value, $id ); ?>><?php echo esc_html( $label ); ?></option>
<?php endforeach; ?>
</select>
</p>
<p>
<label for="cee_away_team_custom" class="screen-reader-text"><?php esc_html_e( 'Nom de l’équipe adverse', 'club-easy-event' ); ?></label>
<input type="text" id="cee_away_team_custom" name="cee_away_team_id_text" class="widefat" value="<?php echo is_string( $away_team_value ) ? esc_attr( $away_team_value ) : ''; ?>" placeholder="<?php esc_attr_e( 'Nom de l’équipe adverse (si externe)', 'club-easy-event' ); ?>" />
</p>
<p>
<label for="cee_venue_id"><strong><?php esc_html_e( 'Lieu', 'club-easy-event' ); ?></strong></label>
<select id="cee_venue_id" name="cee_venue_id" class="widefat">
<option value="0"><?php esc_html_e( '— Sélectionner —', 'club-easy-event' ); ?></option>
<?php foreach ( $venues as $id => $label ) : ?>
<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $venue_id, $id ); ?>><?php echo esc_html( $label ); ?></option>
<?php endforeach; ?>
</select>
</p>
<p>
<label for="cee_home_score"><strong><?php esc_html_e( 'Score domicile', 'club-easy-event' ); ?></strong></label>
<input type="number" id="cee_home_score" name="cee_home_score" min="0" class="small-text" value="<?php echo esc_attr( $home_score ); ?>" />
</p>
<p>
<label for="cee_away_score"><strong><?php esc_html_e( 'Score extérieur', 'club-easy-event' ); ?></strong></label>
<input type="number" id="cee_away_score" name="cee_away_score" min="0" class="small-text" value="<?php echo esc_attr( $away_score ); ?>" />
</p>
