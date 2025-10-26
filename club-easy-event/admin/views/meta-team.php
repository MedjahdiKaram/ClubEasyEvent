<?php
/**
 * Team meta box view.
 *
 * @var array $players
 * @var array $player_ids
 */
?>
<?php wp_nonce_field( 'cee_team_meta', 'cee_team_meta_nonce' ); ?>
<p><?php esc_html_e( 'Sélectionnez les joueurs appartenant à cette équipe.', 'club-easy-event' ); ?></p>
<ul class="cee-team-players">
<?php foreach ( $players as $id => $label ) : ?>
<li>
<label>
<input type="checkbox" name="cee_team_players[]" value="<?php echo esc_attr( $id ); ?>" <?php checked( in_array( (int) $id, $player_ids, true ), true ); ?> />
<?php echo esc_html( $label ); ?>
</label>
</li>
<?php endforeach; ?>
</ul>
