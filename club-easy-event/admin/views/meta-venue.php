<?php
/**
 * Venue meta box view.
 *
 * @var string $address
 * @var string $map_link
 */
?>
<?php wp_nonce_field( 'cee_venue_meta', 'cee_venue_meta_nonce' ); ?>
<p>
<label for="cee_venue_address"><strong><?php esc_html_e( 'Adresse', 'club-easy-event' ); ?></strong></label>
<input type="text" id="cee_venue_address" name="cee_venue_address" class="widefat" value="<?php echo esc_attr( $address ); ?>" />
</p>
<p>
<label for="cee_venue_map_link"><strong><?php esc_html_e( 'Lien Google Maps', 'club-easy-event' ); ?></strong></label>
<input type="url" id="cee_venue_map_link" name="cee_venue_map_link" class="widefat" value="<?php echo esc_attr( $map_link ); ?>" placeholder="<?php echo esc_attr__( 'https://', 'club-easy-event' ); ?>" />
</p>
