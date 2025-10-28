<?php
/**
 * Player meta box view.
 *
 * @var string $number
 * @var string $position
 * @var int    $user_id
 * @var array  $users
 * @var array  $teams
 * @var array  $team_ids
 */
?>
<?php wp_nonce_field( 'cee_player_meta', 'cee_player_meta_nonce' ); ?>
<p>
<label for="cee_player_number"><strong><?php esc_html_e( 'Numéro de maillot', 'club-easy-event' ); ?></strong></label>
<input type="number" id="cee_player_number" name="cee_player_number" min="0" class="small-text" value="<?php echo esc_attr( $number ); ?>" />
</p>
<p>
<label for="cee_player_position"><strong><?php esc_html_e( 'Poste', 'club-easy-event' ); ?></strong></label>
<input type="text" id="cee_player_position" name="cee_player_position" class="widefat" value="<?php echo esc_attr( $position ); ?>" />
</p>
<p>
<label for="cee_player_user_id"><strong><?php esc_html_e( 'Utilisateur lié', 'club-easy-event' ); ?></strong></label>
<select id="cee_player_user_id" name="cee_player_user_id" class="widefat">
<?php foreach ( $users as $id => $label ) : ?>
<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $user_id, $id ); ?>><?php echo esc_html( $label ); ?></option>
<?php endforeach; ?>
</select>
</p>
<div class="cee-assignment" data-assignment="player">
        <p class="description"><?php esc_html_e( 'Associez ce joueur aux équipes internes pour faciliter les convocations.', 'club-easy-event' ); ?></p>
        <label for="cee-player-team-search" class="screen-reader-text"><?php esc_html_e( 'Rechercher une équipe', 'club-easy-event' ); ?></label>
        <input type="search" id="cee-player-team-search" class="cee-assignment-search" placeholder="<?php esc_attr_e( 'Rechercher une équipe…', 'club-easy-event' ); ?>" data-target="cee-player-team-list" />
        <ul id="cee-player-team-list" class="cee-assignment-list">
                <?php foreach ( $teams as $id => $label ) : ?>
                        <li class="cee-assignment-item" data-search-text="<?php echo esc_attr( strtolower( $label ) ); ?>">
                                <label>
                                        <input type="checkbox" name="cee_player_teams[]" value="<?php echo esc_attr( $id ); ?>" <?php checked( in_array( (int) $id, $team_ids, true ), true ); ?> />
                                        <span class="cee-assignment-label"><?php echo esc_html( $label ); ?></span>
                                </label>
                        </li>
                <?php endforeach; ?>
        </ul>
        <p class="description"><?php esc_html_e( 'Les équipes sélectionnées seront également mises à jour côté fiche équipe.', 'club-easy-event' ); ?></p>
</div>
