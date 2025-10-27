<?php
/**
 * Onboarding overlay for admin screens.
 *
 * @package ClubEasyEvent\Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

$steps = array(
        array(
                'title'       => __( 'Introduction', 'club-easy-event' ),
                'description' => __( 'Découvrez les fonctionnalités essentielles de Club Easy Event pour organiser vos compétitions en ligne.', 'club-easy-event' ),
                'link'        => admin_url( 'admin.php?page=cee_dashboard' ),
                'link_label'  => __( 'Voir le tableau de bord', 'club-easy-event' ),
        ),
        array(
                'title'       => __( 'Créer une équipe', 'club-easy-event' ),
                'description' => __( 'Ajoutez vos équipes et gérez leurs informations depuis un seul endroit.', 'club-easy-event' ),
                'link'        => admin_url( 'post-new.php?post_type=cee_team' ),
                'link_label'  => __( 'Créer une équipe', 'club-easy-event' ),
        ),
        array(
                'title'       => __( 'Ajouter des joueurs', 'club-easy-event' ),
                'description' => __( 'Enregistrez vos joueurs et associez-les rapidement à leurs équipes.', 'club-easy-event' ),
                'link'        => admin_url( 'post-new.php?post_type=cee_player' ),
                'link_label'  => __( 'Ajouter un joueur', 'club-easy-event' ),
        ),
        array(
                'title'       => __( 'Créer une ligue', 'club-easy-event' ),
                'description' => __( 'Regroupez vos compétitions en ligues pour structurer vos saisons.', 'club-easy-event' ),
                'link'        => admin_url( 'edit-tags.php?taxonomy=cee_league&post_type=cee_event' ),
                'link_label'  => __( 'Gérer les ligues', 'club-easy-event' ),
        ),
        array(
                'title'       => __( 'Créer un événement', 'club-easy-event' ),
                'description' => __( 'Planifiez vos matchs, entraînements et événements spéciaux avec toutes les informations utiles.', 'club-easy-event' ),
                'link'        => admin_url( 'post-new.php?post_type=cee_event' ),
                'link_label'  => __( 'Créer un événement', 'club-easy-event' ),
        ),
        array(
                'title'       => __( 'Configurer les paramètres', 'club-easy-event' ),
                'description' => __( 'Personnalisez les rappels par e-mail et les couleurs pour refléter l’identité de votre club.', 'club-easy-event' ),
                'link'        => admin_url( 'admin.php?page=cee_settings' ),
                'link_label'  => __( 'Ouvrir les paramètres', 'club-easy-event' ),
        ),
        array(
                'title'       => __( 'Shortcodes et affichage frontal', 'club-easy-event' ),
                'description' => __( 'Intégrez vos équipes et événements sur le site public grâce aux shortcodes disponibles.', 'club-easy-event' ),
                'link'        => plugins_url( 'readme.txt', CEE_PLUGIN_FILE ),
                'link_label'  => __( 'Consulter les shortcodes', 'club-easy-event' ),
        ),
);

$total_steps = count( $steps );
?>
<div class="cee-onboarding" role="region" aria-label="<?php echo esc_attr__( 'Didacticiel Club Easy Event', 'club-easy-event' ); ?>" data-total="<?php echo esc_attr( $total_steps ); ?>">
        <div class="cee-onboarding__header">
                <h2><?php esc_html_e( 'Bienvenue dans Club Easy Event', 'club-easy-event' ); ?></h2>
                <p><?php esc_html_e( 'Suivez ces étapes pour configurer rapidement votre club et vos compétitions.', 'club-easy-event' ); ?></p>
                <span class="cee-onboarding__progress" data-progress-template="<?php echo esc_attr__( 'Étape %1$s sur %2$s', 'club-easy-event' ); ?>"></span>
        </div>
        <div class="cee-onboarding__body">
                <?php foreach ( $steps as $index => $step ) : ?>
                        <div class="cee-onboarding__step<?php echo 0 === $index ? ' is-active' : ''; ?>" data-step="<?php echo esc_attr( $index ); ?>">
                                <h3 class="cee-onboarding__step-title"><?php echo esc_html( $step['title'] ); ?></h3>
                                <p class="cee-onboarding__step-description"><?php echo esc_html( $step['description'] ); ?></p>
                                <?php if ( ! empty( $step['link'] ) ) : ?>
                                        <a class="button button-secondary cee-onboarding__step-link" href="<?php echo esc_url( $step['link'] ); ?>" target="_blank" rel="noopener noreferrer">
                                                <?php echo esc_html( $step['link_label'] ); ?>
                                        </a>
                                <?php endif; ?>
                        </div>
                <?php endforeach; ?>
        </div>
        <div class="cee-onboarding__actions">
                <button type="button" class="button button-primary cee-onboarding__next"><?php esc_html_e( 'Suivant', 'club-easy-event' ); ?></button>
                <button type="button" class="button cee-onboarding__dismiss"><?php esc_html_e( 'Ne plus afficher', 'club-easy-event' ); ?></button>
                <a class="button-link cee-onboarding__docs" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( plugins_url( 'readme.txt', CEE_PLUGIN_FILE ) ); ?>"><?php esc_html_e( 'Voir la documentation', 'club-easy-event' ); ?></a>
        </div>
</div>
