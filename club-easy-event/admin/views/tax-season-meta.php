<?php
/**
 * Season term meta view.
 *
 * @var array $products
 * @var int   $product_id
 * @var bool  $is_edit
 */
?>
<?php if ( ! empty( $products ) ) : ?>
<?php if ( ! empty( $is_edit ) ) : ?>
<tr class="form-field">
<th scope="row"><label for="cee_season_wc_product_id"><?php esc_html_e( 'Produit WooCommerce associé', 'club-easy-event' ); ?></label></th>
<td>
<select id="cee_season_wc_product_id" name="cee_season_wc_product_id">
<option value="0"><?php esc_html_e( '— Aucun —', 'club-easy-event' ); ?></option>
<?php foreach ( $products as $id => $label ) : ?>
<option value="<?php echo esc_attr( $id ); ?>" <?php selected( isset( $product_id ) ? $product_id : 0, $id ); ?>><?php echo esc_html( $label ); ?></option>
<?php endforeach; ?>
</select>
<p class="description"><?php esc_html_e( 'Utilisé pour vérifier si les joueurs ont réglé leur cotisation.', 'club-easy-event' ); ?></p>
</td>
</tr>
<?php else : ?>
<div class="form-field">
<label for="cee_season_wc_product_id"><?php esc_html_e( 'Produit WooCommerce associé', 'club-easy-event' ); ?></label>
<select id="cee_season_wc_product_id" name="cee_season_wc_product_id">
<option value="0"><?php esc_html_e( '— Aucun —', 'club-easy-event' ); ?></option>
<?php foreach ( $products as $id => $label ) : ?>
<option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></option>
<?php endforeach; ?>
</select>
<p class="description"><?php esc_html_e( 'Utilisé pour vérifier si les joueurs ont réglé leur cotisation.', 'club-easy-event' ); ?></p>
</div>
<?php endif; ?>
<?php else : ?>
<?php if ( ! empty( $is_edit ) ) : ?>
<tr class="form-field">
<th scope="row">&nbsp;</th>
<td><p><?php esc_html_e( 'Aucun produit WooCommerce disponible.', 'club-easy-event' ); ?></p></td>
</tr>
<?php else : ?>
<p><?php esc_html_e( 'Aucun produit WooCommerce disponible.', 'club-easy-event' ); ?></p>
<?php endif; ?>
<?php endif; ?>
