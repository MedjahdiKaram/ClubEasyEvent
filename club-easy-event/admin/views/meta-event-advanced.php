<?php
/**
 * Advanced event meta box view.
 *
 * @var array  $teams
 * @var array  $venues
 * @var string $event_date
 * @var string $event_time
 * @var string $event_type
 * @var string $event_type_key
 * @var int    $home_team_id
 * @var mixed  $away_team_value
 * @var int    $venue_id
 * @var string $home_score
 * @var string $away_score
 * @var array  $validation_messages
 */
?>
<?php wp_nonce_field( 'cee_event_meta', 'cee_event_meta_nonce' ); ?>
<?php
$render_messages = static function( $field_key ) use ( $validation_messages ) {
        if ( empty( $validation_messages['fields'][ $field_key ] ) ) {
                return;
        }
        echo '<ul class="cee-field-messages" aria-live="polite">';
        foreach ( $validation_messages['fields'][ $field_key ] as $message ) {
                $type = isset( $message['type'] ) ? $message['type'] : 'info';
                printf(
                        '<li class="cee-field-message cee-field-message--%1$s">%2$s</li>',
                        esc_attr( $type ),
                        esc_html( $message['text'] )
                );
        }
        echo '</ul>';
};
?>
<div class="cee-event-meta-grid">
        <section class="cee-event-group cee-event-group--planning">
                <h3><?php esc_html_e( 'Planning', 'club-easy-event' ); ?></h3>
                <div class="cee-event-field">
                        <label for="cee_event_date" class="cee-event-label"><?php esc_html_e( 'Date', 'club-easy-event' ); ?></label>
                        <input type="date" id="cee_event_date" name="cee_event_date" class="cee-date-field" value="<?php echo esc_attr( $event_date ); ?>" aria-describedby="cee-event-date-help" />
                        <p id="cee-event-date-help" class="description"><?php esc_html_e( 'Utilisez le format ISO AAAA-MM-JJ.', 'club-easy-event' ); ?></p>
                        <?php $render_messages( 'date' ); ?>
                </div>
                <div class="cee-event-field">
                        <label for="cee_event_time" class="cee-event-label"><?php esc_html_e( 'Heure', 'club-easy-event' ); ?></label>
                        <input type="time" id="cee_event_time" name="cee_event_time" class="cee-time-field" value="<?php echo esc_attr( $event_time ); ?>" aria-describedby="cee-event-time-help" />
                        <p id="cee-event-time-help" class="description"><?php esc_html_e( 'Format attendu : HH:MM (24h).', 'club-easy-event' ); ?></p>
                        <?php $render_messages( 'time' ); ?>
                </div>
                <div class="cee-event-field">
                        <label for="cee_event_type" class="cee-event-label"><?php esc_html_e( 'Type d’événement', 'club-easy-event' ); ?></label>
                        <select id="cee_event_type" name="cee_event_type" class="widefat">
                                <?php foreach ( $event_types as $type_key => $type_label ) : ?>
                                        <option value="<?php echo esc_attr( $type_key ); ?>" <?php selected( $event_type_key, $type_key ); ?>><?php echo esc_html( $type_label ); ?></option>
                                <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Ce choix influence les validations et la communication aux équipes.', 'club-easy-event' ); ?></p>
                </div>
        </section>
        <section class="cee-event-group cee-event-group--participants">
                <h3><?php esc_html_e( 'Participants & Lieu', 'club-easy-event' ); ?></h3>
                <div class="cee-event-field">
                        <label for="cee_home_team_id" class="cee-event-label"><?php esc_html_e( 'Équipe à domicile', 'club-easy-event' ); ?></label>
                        <select id="cee_home_team_id" name="cee_home_team_id" class="widefat">
                                <option value="0"><?php esc_html_e( '— Sélectionner —', 'club-easy-event' ); ?></option>
                                <?php foreach ( $teams as $id => $label ) : ?>
                                        <option value="<?php echo esc_attr( $id ); ?>" <?php selected( $home_team_id, $id ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                        </select>
                        <?php $render_messages( 'home_team' ); ?>
                </div>
                <div class="cee-event-field">
                        <label for="cee_away_team_select" class="cee-event-label"><?php esc_html_e( 'Équipe adverse', 'club-easy-event' ); ?></label>
                        <select id="cee_away_team_select" name="cee_away_team_id_select" class="widefat">
                                <option value=""><?php esc_html_e( '— Équipe externe (voir champ texte) —', 'club-easy-event' ); ?></option>
                                <?php foreach ( $teams as $id => $label ) : ?>
                                        <option value="<?php echo esc_attr( $id ); ?>" <?php selected( (int) $away_team_value, $id ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                        </select>
                        <input type="text" id="cee_away_team_custom" name="cee_away_team_id_text" class="widefat" value="<?php echo is_string( $away_team_value ) ? esc_attr( $away_team_value ) : ''; ?>" placeholder="<?php esc_attr_e( 'Nom de l’équipe adverse (si externe)', 'club-easy-event' ); ?>" />
                        <?php $render_messages( 'away_team' ); ?>
                </div>
                <div class="cee-event-field">
                        <label for="cee_venue_id" class="cee-event-label"><?php esc_html_e( 'Lieu', 'club-easy-event' ); ?></label>
                        <select id="cee_venue_id" name="cee_venue_id" class="widefat">
                                <option value="0"><?php esc_html_e( '— Sélectionner —', 'club-easy-event' ); ?></option>
                                <?php foreach ( $venues as $id => $label ) : ?>
                                        <option value="<?php echo esc_attr( $id ); ?>" <?php selected( $venue_id, $id ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                        </select>
                        <?php
                        $create_venue_url   = admin_url( 'post-new.php?post_type=cee_venue' );
                        $should_show_notice = isset( $venues_notice ) ? (bool) $venues_notice : empty( $venues );
                        if ( $should_show_notice ) :
                                printf(
                                        '<div class="notice notice-warning inline cee-venue-notice">%s</div>',
                                        wp_kses(
                                                sprintf(
                                                        /* translators: %s: link to create a new venue */
                                                        __( 'Aucun lieu approuvé trouvé. %s', 'club-easy-event' ),
                                                        sprintf(
                                                                '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
                                                                esc_url( $create_venue_url ),
                                                                esc_html__( 'Ajouter un lieu', 'club-easy-event' )
                                                        )
                                                ),
                                                array(
                                                        'a' => array(
                                                                'href'   => true,
                                                                'target' => true,
                                                                'rel'    => true,
                                                        ),
                                                )
                                        )
                                );
                        endif;
                        ?>
                        <?php $render_messages( 'venue' ); ?>
                </div>
        </section>
        <section class="cee-event-group cee-event-group--results">
                <h3><?php esc_html_e( 'Résultat', 'club-easy-event' ); ?></h3>
                <div class="cee-event-field-inline">
                        <label for="cee_home_score" class="cee-event-label"><?php esc_html_e( 'Score domicile', 'club-easy-event' ); ?></label>
                        <input type="number" id="cee_home_score" name="cee_home_score" min="0" class="small-text" value="<?php echo esc_attr( $home_score ); ?>" />
                </div>
                <div class="cee-event-field-inline">
                        <label for="cee_away_score" class="cee-event-label"><?php esc_html_e( 'Score extérieur', 'club-easy-event' ); ?></label>
                        <input type="number" id="cee_away_score" name="cee_away_score" min="0" class="small-text" value="<?php echo esc_attr( $away_score ); ?>" />
                </div>
        </section>
</div>
<?php if ( ! empty( $validation_messages['global'] ) ) : ?>
        <div class="cee-event-validation-summary" role="alert">
                <strong><?php esc_html_e( 'Points à vérifier', 'club-easy-event' ); ?></strong>
                <ul>
                        <?php foreach ( $validation_messages['global'] as $message ) : ?>
                                <li><?php echo esc_html( $message ); ?></li>
                        <?php endforeach; ?>
                </ul>
        </div>
<?php endif; ?>
