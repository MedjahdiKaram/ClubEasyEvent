<?php
/**
 * Team meta box view.
 *
 * @var array $players
 * @var array $player_ids
 */
?>
<?php wp_nonce_field( 'cee_team_meta', 'cee_team_meta_nonce' ); ?>
<div class="cee-assignment" data-assignment="team">
        <p class="description"><?php esc_html_e( 'Associez rapidement les joueurs à cette équipe. Utilisez la recherche pour filtrer.', 'club-easy-event' ); ?></p>
        <label for="cee-team-player-search" class="screen-reader-text"><?php esc_html_e( 'Rechercher un joueur', 'club-easy-event' ); ?></label>
        <input type="search" id="cee-team-player-search" class="cee-assignment-search" placeholder="<?php esc_attr_e( 'Rechercher un joueur…', 'club-easy-event' ); ?>" data-target="cee-team-player-list" />
        <ul id="cee-team-player-list" class="cee-assignment-list">
                <?php foreach ( $players as $id => $label ) : ?>
                        <li class="cee-assignment-item" data-search-text="<?php echo esc_attr( strtolower( $label ) ); ?>">
                                <label>
                                        <input type="checkbox" name="cee_team_players[]" value="<?php echo esc_attr( $id ); ?>" <?php checked( in_array( (int) $id, $player_ids, true ), true ); ?> />
                                        <span class="cee-assignment-label"><?php echo esc_html( $label ); ?></span>
                                </label>
                        </li>
                <?php endforeach; ?>
        </ul>
        <p class="description"><?php esc_html_e( 'Les changements sont synchronisés automatiquement avec le profil de chaque joueur.', 'club-easy-event' ); ?></p>
</div>
