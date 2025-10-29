# Guide Développeur

## Architecture
- Toutes les classes sont chargées via `includes/class-cee-plugin.php` et branchées grâce à `CEE_Loader`.
- Classes clés :
  - `CEE_CPT`, `CEE_Taxonomies`, `CEE_Meta` pour les contenus personnalisés et leurs métadonnées.
  - `CEE_Approval` pour le workflow d’approbation (meta-box, filtres, bulk actions, badges).
  - `CEE_Assignment` pour la synchronisation joueurs ↔ équipes et les helpers d’assignation.
  - `CEE_Notifications` pour détecter les changements d’événement et envoyer les e-mails correspondants.
  - `CEE_Shortcodes` et `CEE_Shortcode_Player_Signup` pour les rendus front.
  - `CEE_Settings`, `CEE_Admin`, `CEE_Admin_Columns`, `CEE_Frontend`, `CEE_WooCommerce` pour les écrans de réglages, colonnes, assets et intégrations.
- Le text-domain unique reste `club-easy-event`.

## Hooks principaux
- `cee_schedule_query_args( $args, $atts )` – Ajuster la requête du shortcode `[cee_schedule]`.
- `cee_event_shortcode_output( $output, $event_id, $atts )` – Filtrer le HTML du shortcode `[cee_event]`.
- `cee_events_bulk_shift_days( $post_ids, $offset )` – Déclenché après un décalage groupé des dates.
- `cee_roles_manager_allowed_roles( $roles )` – Restreindre les rôles disponibles dans l’écran de gestion.
- `cee_rsvp_updated( $event_id, $user_id, $response )` – Notifié lors d’une mise à jour RSVP.
- `cee_primary_color( $hex )` – Modifier la couleur principale front-end.
- `cee_can_approve( $can, $post_id, $state )` – Filtrer dynamiquement les autorisations de transition d’un état d’approbation.
- `cee_event_validation_rules( $rules )` – Étendre les validations d’assignation d’un événement.
- `cee_event_update_recipients( $emails, $event_id )` – Ajouter/supprimer des destinataires de notification.
- `cee_event_update_template( $subject, $body, $event_id, $diff )` – Ajuster le contenu des e-mails de mise à jour.
- `cee_player_signup_default_role( $role, $values )`, `cee_player_signup_default_team_id( $team_id, $player_id, $values )`, `cee_player_signup_enabled_default( $enabled, $values )`, `cee_player_signup_success_message( $message, $values )` – Affiner le workflow d’inscription.
- `cee_player_team_assignment_changed( $player_id, $team_ids )` – Notifié lors d’une mise à jour des équipes d’un joueur.
- `cee_player_signup_notification_recipients( $emails, $player_id, $user_id, $values )` & `cee_player_signup_send_confirmation( $send, $player_id, $user_id, $values )` – Contrôler les e-mails liés au formulaire.
- `cee_approval_post_types( $post_types )` – Ajouter/retirer des types de contenus gérés par l’approbation.
- `cee_venue_dropdown_query_args( $args )` – Modifier les paramètres de la requête des lieux approuvés dans l’éditeur d’événement.

## Scripts & i18n
- Scripts admin : `cee-datetime-enhance`, `cee-roles-manager`, `cee-onboarding`, `cee-approval`, `cee-assignment`, `cee-dashboard` (tous déclarés avec `wp_set_script_translations`).
- Scripts front : `club-easy-event-public` et `club-easy-event-public-modern` (utilisent `wp_set_script_translations` + `wp.i18n`).
- Ajouter systématiquement les chaînes PHP via `__()`, `_e()`, `esc_html__()`… et déclarer les chaînes JS via `wp_set_script_translations` ou `wp.i18n.__`.

## Assignation & métadonnées
- `CEE_Assignment::sync_team_players( $team_id, $player_ids )` et `CEE_Assignment::sync_player_teams( $player_id, $team_ids )` maintiennent la relation bidirectionnelle (`_cee_team_players` ↔ `_cee_player_teams`).
- Les meta-boxes d’assignation utilisent des listes filtrables et la classe applique des vérifications de capacités (`edit_post`).

## Workflow d’approbation
- Les états sont stockés en post meta (`_cee_approval_state`, `_cee_approved_by`, `_cee_approved_at`, `_cee_approval_note`).
- Les transitions vérifient les capacités et déclenchent `do_action( 'cee_approval_state_changed', $post_id, $old_state, $new_state )` (voir le code si besoin d’intégration).
- Les colonnes d’administration se servent de `CEE_Admin_Columns::get_approval_badge()` pour générer les badges.

## Notifications
- `CEE_Notifications::capture_previous_snapshot()` sauvegarde la configuration avant mise à jour, puis `::detect_changes()` compare les valeurs.
- `CEE_Notifications::send_event_update_notifications( $event_id, $diff )` génère les destinataires (joueurs + managers) et applique un throttling par transient (`cee_event_notice_{md5}`).
- Les gabarits sont personnalisables via l’interface de réglages et les filtres listés plus haut.

## Shortcodes & formulaire d’inscription
- `CEE_Shortcode_Player_Signup` gère la validation (nonce, honeypot, limite IP/email) et la création de l’utilisateur + `cee_player`. Les champs sont nettoyés avec `sanitize_email`, `sanitize_text_field`, `sanitize_textarea_field` et validations supplémentaires (âge, téléphones).
- Le shortcode signale au front (`CEE_Frontend::mark_assets_needed()`) de charger les assets modernes.
- Des hooks (`cee_player_signup_created`, `cee_player_signup_notification_recipients`, etc.) permettent d’intégrer CRM ou automatisations externes.

## Internationalisation
- `.pot` et `.po` (en_US, es_ES) couvrent toutes les nouvelles chaînes, aucun `.mo` n’est versionné.
- Utilisez `wp i18n make-pot . languages/club-easy-event.pot --exclude="vendor"` pour régénérer le catalogue.
- Les scripts front modernes utilisent `wp_set_script_translations` pour afficher l’alerte sur les mineurs et autres messages dynamiques.
