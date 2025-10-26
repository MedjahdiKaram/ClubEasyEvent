<?php
/**
 * Player meta box view.
 *
 * @var string $number
 * @var string $position
 * @var int    $user_id
 * @var array  $users
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
