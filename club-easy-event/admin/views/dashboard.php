<?php
/**
 * Plugin dashboard view.
 *
 * @var array $dashboard_kpis
 * @var array $upcoming_events
 * @var array $pending_events
 * @var array $recent_events
 * @var array $quick_actions
 * @var bool  $can_moderate
 * @var string $today
 * @var string $upcoming_range_end
 */

$format_schedule = static function( $date, $time ) {
        $timezone   = function_exists( 'wp_timezone' ) ? wp_timezone() : null;
        $date_label = '';

        if ( $date ) {
                $date_object = date_create_from_format( 'Y-m-d', $date, $timezone );
                if ( $date_object instanceof DateTimeInterface ) {
                        $date_label = wp_date( get_option( 'date_format' ), $date_object->getTimestamp() );
                } else {
                        $date_label = $date;
                }
        } else {
                $date_label = __( 'À planifier', 'club-easy-event' );
        }

        if ( $time ) {
                $time_object = date_create_from_format( 'H:i', $time, $timezone );
                $time_label  = $time_object instanceof DateTimeInterface ? wp_date( get_option( 'time_format' ), $time_object->getTimestamp() ) : $time;
                return sprintf( '%1$s • %2$s', $date_label, $time_label );
        }

        return $date_label;
};

$format_modified = static function( $modified ) {
        if ( empty( $modified ) ) {
                return __( 'Date de modification inconnue', 'club-easy-event' );
        }
        $timestamp = strtotime( $modified );
        if ( false === $timestamp ) {
                return $modified;
        }
        return wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
};

$format_team_line = static function( $home, $away ) {
        if ( $home && $away ) {
                return sprintf( __( '%1$s vs %2$s', 'club-easy-event' ), $home, $away );
        }
        if ( $home ) {
                return sprintf( __( 'Accueil : %s', 'club-easy-event' ), $home );
        }
        if ( $away ) {
                return sprintf( __( 'Adversaire : %s', 'club-easy-event' ), $away );
        }
        return '';
};

$today_label         = $today ? wp_date( get_option( 'date_format' ), strtotime( $today ) ) : '';
$range_end_label     = $upcoming_range_end ? wp_date( get_option( 'date_format' ), strtotime( $upcoming_range_end ) ) : '';
$pending_overview_url = admin_url( 'edit.php?post_type=cee_event&approval_state=pending' );
$events_overview_url  = admin_url( 'edit.php?post_type=cee_event' );
?>
<div class="wrap cee-dashboard">
        <h1><?php esc_html_e( 'Tableau de bord — Club Easy Event', 'club-easy-event' ); ?></h1>
        <p class="cee-dashboard__intro"><?php esc_html_e( 'Suivez vos chiffres clés, vos prochains événements et les validations en attente.', 'club-easy-event' ); ?></p>

        <div class="cee-dashboard__kpis cee-grid">
                <div class="cee-card cee-card--kpi">
                        <h2 class="cee-card__title"><?php esc_html_e( 'Équipes', 'club-easy-event' ); ?></h2>
                        <p class="cee-card__metric"><?php echo esc_html( number_format_i18n( $dashboard_kpis['teams'] ) ); ?></p>
                        <p class="cee-card__hint"><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=cee_team' ) ); ?>"><?php esc_html_e( 'Gérer les équipes', 'club-easy-event' ); ?></a></p>
                </div>
                <div class="cee-card cee-card--kpi">
                        <h2 class="cee-card__title"><?php esc_html_e( 'Joueurs', 'club-easy-event' ); ?></h2>
                        <p class="cee-card__metric"><?php echo esc_html( number_format_i18n( $dashboard_kpis['players'] ) ); ?></p>
                        <p class="cee-card__hint"><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=cee_player' ) ); ?>"><?php esc_html_e( 'Gérer les joueurs', 'club-easy-event' ); ?></a></p>
                </div>
                <div class="cee-card cee-card--kpi">
                        <h2 class="cee-card__title"><?php esc_html_e( 'Évènements à venir (7 jours)', 'club-easy-event' ); ?></h2>
                        <p class="cee-card__metric"><?php echo esc_html( number_format_i18n( $dashboard_kpis['upcoming'] ) ); ?></p>
                        <p class="cee-card__hint"><a href="<?php echo esc_url( $events_overview_url ); ?>"><?php esc_html_e( 'Voir tous les évènements', 'club-easy-event' ); ?></a></p>
                </div>
                <div class="cee-card cee-card--kpi">
                        <h2 class="cee-card__title"><?php esc_html_e( 'Évènements à valider', 'club-easy-event' ); ?></h2>
                        <p class="cee-card__metric"><?php echo esc_html( number_format_i18n( $dashboard_kpis['pending'] ) ); ?></p>
                        <p class="cee-card__hint"><a href="<?php echo esc_url( $pending_overview_url ); ?>"><?php esc_html_e( 'Filtrer les évènements en attente', 'club-easy-event' ); ?></a></p>
                </div>
        </div>

        <div class="cee-dashboard__layout">
                <div class="cee-dashboard__column cee-dashboard__column--primary">
                        <section class="cee-card cee-card--panel" data-cee-collapsible="true">
                                <header class="cee-card__header">
                                        <h2 class="cee-card__title"><?php esc_html_e( 'Prochains évènements', 'club-easy-event' ); ?></h2>
                                        <?php if ( $today_label && $range_end_label ) : ?>
                                                <p class="cee-card__subtitle"><?php printf( esc_html__( '%1$s → %2$s', 'club-easy-event' ), esc_html( $today_label ), esc_html( $range_end_label ) ); ?></p>
                                        <?php endif; ?>
                                        <button type="button" class="cee-card__toggle" aria-expanded="true" data-expanded-icon="−" data-collapsed-icon="+">
                                                <span class="screen-reader-text"><?php esc_html_e( 'Basculer la section', 'club-easy-event' ); ?></span>
                                                <span class="cee-card__toggle-icon" aria-hidden="true">−</span>
                                        </button>
                                </header>
                                <div class="cee-card__body">
                                <?php if ( ! empty( $upcoming_events ) ) : ?>
                                        <ul class="cee-card__list">
                                                <?php foreach ( $upcoming_events as $event ) :
                                                        $schedule  = $format_schedule( $event['date'], $event['time'] );
                                                        $teams     = $format_team_line( $event['home_team'], $event['away_team'] );
                                                        $badge     = ( $event['approval_state'] && class_exists( 'CEE_Approval' ) ) ? CEE_Approval::get_state_badge( $event['approval_state'] ) : '';
                                                        $title     = $event['title'] ? $event['title'] : __( 'Évènement sans titre', 'club-easy-event' );
                                                ?>
                                                        <li class="cee-card__item">
                                                                <div class="cee-card__item-header">
                                                                        <?php if ( $event['edit_link'] ) : ?>
                                                                                <a class="cee-card__item-title" href="<?php echo esc_url( $event['edit_link'] ); ?>"><?php echo esc_html( $title ); ?></a>
                                                                        <?php else : ?>
                                                                                <span class="cee-card__item-title"><?php echo esc_html( $title ); ?></span>
                                                                        <?php endif; ?>
                                                                        <?php if ( $badge ) : ?>
                                                                                <span class="cee-card__badge"><?php echo wp_kses_post( $badge ); ?></span>
                                                                        <?php endif; ?>
                                                                </div>
                                                                <div class="cee-card__meta">
                                                                        <span class="cee-card__meta-item cee-card__meta-item--date"><?php echo esc_html( $schedule ); ?></span>
                                                                        <?php if ( $teams ) : ?>
                                                                                <span class="cee-card__meta-item cee-card__meta-item--teams"><?php echo esc_html( $teams ); ?></span>
                                                                        <?php endif; ?>
                                                                        <?php if ( $event['venue'] ) : ?>
                                                                                <span class="cee-card__meta-item cee-card__meta-item--venue"><?php echo esc_html( $event['venue'] ); ?></span>
                                                                        <?php endif; ?>
                                                                </div>
                                                        </li>
                                                <?php endforeach; ?>
                                        </ul>
                                <?php else : ?>
                                        <p class="cee-card__empty"><?php esc_html_e( 'Aucun évènement à venir dans les 7 prochains jours.', 'club-easy-event' ); ?></p>
                                <?php endif; ?>
                                </div>
                        </section>

                        <?php if ( $can_moderate ) : ?>
                        <section class="cee-card cee-card--panel" data-cee-collapsible="true">
                                <header class="cee-card__header">
                                        <h2 class="cee-card__title"><?php esc_html_e( 'À valider', 'club-easy-event' ); ?></h2>
                                        <p class="cee-card__subtitle"><?php esc_html_e( 'Évènements en attente d’approbation.', 'club-easy-event' ); ?></p>
                                        <button type="button" class="cee-card__toggle" aria-expanded="true" data-expanded-icon="−" data-collapsed-icon="+">
                                                <span class="screen-reader-text"><?php esc_html_e( 'Basculer la section', 'club-easy-event' ); ?></span>
                                                <span class="cee-card__toggle-icon" aria-hidden="true">−</span>
                                        </button>
                                </header>
                                <div class="cee-card__body">
                                <?php if ( ! empty( $pending_events ) ) : ?>
                                        <ul class="cee-card__list">
                                                <?php foreach ( $pending_events as $event ) :
                                                        $schedule  = $format_schedule( $event['date'], $event['time'] );
                                                        $teams     = $format_team_line( $event['home_team'], $event['away_team'] );
                                                        $title     = $event['title'] ? $event['title'] : __( 'Évènement sans titre', 'club-easy-event' );
                                                ?>
                                                        <li class="cee-card__item">
                                                                <div class="cee-card__item-header">
                                                                        <?php if ( $event['edit_link'] ) : ?>
                                                                                <a class="cee-card__item-title" href="<?php echo esc_url( $event['edit_link'] ); ?>"><?php echo esc_html( $title ); ?></a>
                                                                        <?php else : ?>
                                                                                <span class="cee-card__item-title"><?php echo esc_html( $title ); ?></span>
                                                                        <?php endif; ?>
                                                                        <span class="cee-card__badge cee-card__badge--pending"><?php esc_html_e( 'En attente', 'club-easy-event' ); ?></span>
                                                                </div>
                                                                <div class="cee-card__meta">
                                                                        <span class="cee-card__meta-item cee-card__meta-item--date"><?php echo esc_html( $schedule ); ?></span>
                                                                        <?php if ( $teams ) : ?>
                                                                                <span class="cee-card__meta-item cee-card__meta-item--teams"><?php echo esc_html( $teams ); ?></span>
                                                                        <?php endif; ?>
                                                                        <?php if ( $event['venue'] ) : ?>
                                                                                <span class="cee-card__meta-item cee-card__meta-item--venue"><?php echo esc_html( $event['venue'] ); ?></span>
                                                                        <?php endif; ?>
                                                                </div>
                                                        </li>
                                                <?php endforeach; ?>
                                        </ul>
                                <?php else : ?>
                                        <p class="cee-card__empty"><?php esc_html_e( 'Aucun évènement n’attend de validation pour le moment.', 'club-easy-event' ); ?></p>
                                <?php endif; ?>
                                <p class="cee-card__footer"><a class="button button-primary" href="<?php echo esc_url( $pending_overview_url ); ?>"><?php esc_html_e( 'Gérer les validations', 'club-easy-event' ); ?></a></p>
                                </div>
                        </section>
                        <?php endif; ?>
                </div>

                <div class="cee-dashboard__column cee-dashboard__column--secondary">
                        <section class="cee-card cee-card--panel" data-cee-collapsible="true">
                                <header class="cee-card__header">
                                        <h2 class="cee-card__title"><?php esc_html_e( 'Actions rapides', 'club-easy-event' ); ?></h2>
                                        <button type="button" class="cee-card__toggle" aria-expanded="true" data-expanded-icon="−" data-collapsed-icon="+">
                                                <span class="screen-reader-text"><?php esc_html_e( 'Basculer la section', 'club-easy-event' ); ?></span>
                                                <span class="cee-card__toggle-icon" aria-hidden="true">−</span>
                                        </button>
                                </header>
                                <div class="cee-card__body">
                                <ul class="cee-actions">
                                        <?php foreach ( $quick_actions as $action ) : ?>
                                                <li class="cee-actions__item">
                                                        <a class="button button-secondary" href="<?php echo esc_url( $action['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $action['label'] ); ?></a>
                                                </li>
                                        <?php endforeach; ?>
                                </ul>
                                </div>
                        </section>

                        <section class="cee-card cee-card--panel" data-cee-collapsible="true">
                                <header class="cee-card__header">
                                        <h2 class="cee-card__title"><?php esc_html_e( 'Dernières modifications', 'club-easy-event' ); ?></h2>
                                        <p class="cee-card__subtitle"><?php esc_html_e( 'Suivez les mises à jour récentes des évènements.', 'club-easy-event' ); ?></p>
                                        <button type="button" class="cee-card__toggle" aria-expanded="true" data-expanded-icon="−" data-collapsed-icon="+">
                                                <span class="screen-reader-text"><?php esc_html_e( 'Basculer la section', 'club-easy-event' ); ?></span>
                                                <span class="cee-card__toggle-icon" aria-hidden="true">−</span>
                                        </button>
                                </header>
                                <div class="cee-card__body">
                                <?php if ( ! empty( $recent_events ) ) : ?>
                                        <ul class="cee-card__list">
                                                <?php foreach ( $recent_events as $event ) :
                                                        $modified = $format_modified( $event['modified'] );
                                                        $title    = $event['title'] ? $event['title'] : __( 'Évènement sans titre', 'club-easy-event' );
                                                ?>
                                                        <li class="cee-card__item">
                                                                <div class="cee-card__item-header">
                                                                        <?php if ( $event['edit_link'] ) : ?>
                                                                                <a class="cee-card__item-title" href="<?php echo esc_url( $event['edit_link'] ); ?>"><?php echo esc_html( $title ); ?></a>
                                                                        <?php else : ?>
                                                                                <span class="cee-card__item-title"><?php echo esc_html( $title ); ?></span>
                                                                        <?php endif; ?>
                                                                </div>
                                                                <div class="cee-card__meta">
                                                                        <span class="cee-card__meta-item cee-card__meta-item--status"><?php echo esc_html( $event['status_label'] ? $event['status_label'] : __( 'Statut inconnu', 'club-easy-event' ) ); ?></span>
                                                                        <span class="cee-card__meta-item cee-card__meta-item--modified"><?php echo esc_html( $modified ); ?></span>
                                                                </div>
                                                        </li>
                                                <?php endforeach; ?>
                                        </ul>
                                <?php else : ?>
                                        <p class="cee-card__empty"><?php esc_html_e( 'Aucune modification récente détectée.', 'club-easy-event' ); ?></p>
                                <?php endif; ?>
                                </div>
                        </section>
                </div>
        </div>
</div>
