<?php
/**
 * Roles manager view.
 *
 * @var WP_User[] $users
 * @var array     $role_names
 * @var int       $total
 * @var int       $per_page
 * @var int       $paged
 * @var int       $total_pages
 * @var string    $search
 * @var array     $roles
 * @var array     $notice_data
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}
?>
<div class="wrap cee-roles-manager">
        <h1><?php esc_html_e( 'Gestion des rôles', 'club-easy-event' ); ?></h1>
        <p class="description"><?php esc_html_e( 'Attribuez rapidement des rôles aux utilisateurs de votre club. Seuls les administrateurs peuvent accéder à cette page.', 'club-easy-event' ); ?></p>

        <?php if ( ! empty( $notice_data['message'] ) ) :
                $type  = isset( $notice_data['type'] ) ? $notice_data['type'] : 'updated';
                $class = 'notice';
                if ( 'error' === $type ) {
                        $class .= ' notice-error';
                } elseif ( 'updated' === $type ) {
                        $class .= ' notice-success';
                } elseif ( 'notice-warning' === $type ) {
                        $class .= ' notice-warning';
                } else {
                        $class .= ' notice-info';
                }
                ?>
                <div class="<?php echo esc_attr( $class ); ?>"><p><?php echo esc_html( $notice_data['message'] ); ?></p></div>
        <?php endif; ?>

        <form method="get" class="cee-roles-search" role="search">
                <input type="hidden" name="page" value="cee_roles" />
                <label for="cee-roles-search" class="screen-reader-text"><?php esc_html_e( 'Rechercher des utilisateurs', 'club-easy-event' ); ?></label>
                <input type="search" id="cee-roles-search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Rechercher par nom ou e-mail…', 'club-easy-event' ); ?>" />
                <button type="submit" class="button button-secondary"><?php esc_html_e( 'Rechercher', 'club-easy-event' ); ?></button>
        </form>

        <div class="cee-roles-toolbar">
                <input type="search" id="cee-roles-filter" class="cee-roles-filter" aria-label="<?php echo esc_attr__( 'Filtrer la liste des utilisateurs', 'club-easy-event' ); ?>" placeholder="<?php esc_attr_e( 'Filtrer rapidement…', 'club-easy-event' ); ?>" />
                <span class="cee-roles-count"><?php printf( esc_html( _n( '%d utilisateur trouvé', '%d utilisateurs trouvés', $total, 'club-easy-event' ) ), (int) $total ); ?></span>
        </div>

        <form method="post">
                <input type="hidden" name="page" value="cee_roles" />
                <input type="hidden" name="s" value="<?php echo esc_attr( $search ); ?>" />
                <?php wp_nonce_field( 'cee_roles_bulk', 'cee_roles_bulk_nonce' ); ?>
                <div class="cee-roles-bulk-actions">
                        <div class="cee-roles-bulk-left">
                                <label class="cee-roles-select-all" for="cee-roles-select-all">
                                        <input type="checkbox" id="cee-roles-select-all" />
                                        <span><?php esc_html_e( 'Tout sélectionner', 'club-easy-event' ); ?></span>
                                </label>
                        </div>
                        <div class="cee-roles-bulk-right">
                                <label for="cee_roles_bulk_action" class="screen-reader-text"><?php esc_html_e( 'Action groupée', 'club-easy-event' ); ?></label>
                                <select name="cee_roles_bulk_action" id="cee_roles_bulk_action">
                                        <option value="">— <?php esc_html_e( 'Action groupée', 'club-easy-event' ); ?> —</option>
                                        <option value="assign"><?php esc_html_e( 'Affecter les rôles sélectionnés', 'club-easy-event' ); ?></option>
                                        <option value="remove"><?php esc_html_e( 'Retirer les rôles sélectionnés', 'club-easy-event' ); ?></option>
                                </select>
                                <fieldset class="cee-roles-bulk-roles">
                                        <legend><?php esc_html_e( 'Rôles à appliquer', 'club-easy-event' ); ?></legend>
                                        <?php foreach ( $role_names as $role => $label ) : ?>
                                                <label>
                                                        <input type="checkbox" name="cee_roles_bulk_roles[]" value="<?php echo esc_attr( $role ); ?>" />
                                                        <span><?php echo esc_html( $label ); ?></span>
                                                </label>
                                        <?php endforeach; ?>
                                </fieldset>
                                <button type="submit" class="button button-primary" name="cee_roles_bulk_submit" value="1"><?php esc_html_e( 'Appliquer', 'club-easy-event' ); ?></button>
                        </div>
                </div>

                <table class="widefat fixed striped cee-roles-manager-table" aria-describedby="cee-roles-description">
                        <thead>
                                <tr>
                                        <td class="manage-column column-cb check-column"><input type="checkbox" class="cee-roles-header-checkbox" /></td>
                                        <th scope="col"><?php esc_html_e( 'Utilisateur', 'club-easy-event' ); ?></th>
                                        <th scope="col"><?php esc_html_e( 'Rôles', 'club-easy-event' ); ?></th>
                                        <th scope="col" class="column-actions"><?php esc_html_e( 'Actions', 'club-easy-event' ); ?></th>
                                </tr>
                        </thead>
                        <tbody>
                                <?php if ( empty( $users ) ) : ?>
                                        <tr>
                                                <td colspan="4"><?php esc_html_e( 'Aucun utilisateur ne correspond à votre recherche.', 'club-easy-event' ); ?></td>
                                        </tr>
                                <?php else : ?>
                                        <?php foreach ( $users as $user ) :
                                                $user_roles = array_intersect( (array) $user->roles, array_keys( $role_names ) );
                                                ?>
                                                <tr data-search="<?php echo esc_attr( strtolower( $user->display_name . ' ' . $user->user_email ) ); ?>">
                                                        <th scope="row" class="check-column"><input type="checkbox" name="user_ids[]" value="<?php echo esc_attr( $user->ID ); ?>" /></th>
                                                        <td class="column-primary">
                                                                <strong><?php echo esc_html( $user->display_name ); ?></strong>
                                                                <p class="description"><?php echo esc_html( $user->user_email ); ?></p>
                                                        </td>
                                                        <td class="cee-roles-list">
                                                                <?php foreach ( $role_names as $role => $label ) :
                                                                        $checked = in_array( $role, $user_roles, true );
                                                                        ?>
                                                                        <label>
                                                                                <input type="checkbox" name="cee_roles[<?php echo esc_attr( $user->ID ); ?>][]" value="<?php echo esc_attr( $role ); ?>" <?php checked( $checked ); ?> />
                                                                                <span><?php echo esc_html( $label ); ?></span>
                                                                        </label>
                                                                <?php endforeach; ?>
                                                        </td>
                                                        <td class="cee-roles-actions">
                                                                <input type="hidden" name="cee_roles_single_nonce[<?php echo esc_attr( $user->ID ); ?>]" value="<?php echo esc_attr( wp_create_nonce( 'cee_roles_single_' . $user->ID ) ); ?>" />
                                                                <button type="submit" class="button button-secondary" name="cee_roles_single_update" value="<?php echo esc_attr( $user->ID ); ?>">
                                                                        <?php esc_html_e( 'Mettre à jour', 'club-easy-event' ); ?>
                                                                </button>
                                                        </td>
                                                </tr>
                                        <?php endforeach; ?>
                                <?php endif; ?>
                        </tbody>
                </table>
        </form>

        <?php if ( $total_pages > 1 ) :
                $page_links = paginate_links(
                        array(
                                'base'      => add_query_arg( array( 'paged' => '%#%', 's' => $search, 'page' => 'cee_roles' ), admin_url( 'admin.php' ) ),
                                'format'    => '',
                                'prev_text' => __( '&laquo; Précédent', 'club-easy-event' ),
                                'next_text' => __( 'Suivant &raquo;', 'club-easy-event' ),
                                'total'     => $total_pages,
                                'current'   => $paged,
                        )
                );
                ?>
                <div class="tablenav">
                        <div class="tablenav-pages"><?php echo wp_kses_post( $page_links ); ?></div>
                </div>
        <?php endif; ?>

        <p id="cee-roles-description" class="cee-roles-help description"><?php esc_html_e( 'Cochez les rôles à conserver pour chaque utilisateur puis cliquez sur « Mettre à jour ». Les actions groupées permettent d’ajouter ou de retirer des rôles en une seule fois.', 'club-easy-event' ); ?></p>
</div>
