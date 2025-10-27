# Guide Développeur

## Architecture
- Toutes les classes sont autoloadées via `includes/class-cee-plugin.php`.
- Les classes clés : `CEE_CPT` (types de contenus), `CEE_Meta` (metaboxes & sanitisation), `CEE_Admin` (menus, assets, actions rapides), `CEE_Roles_Manager` (écran d’administration dédié), `CEE_Shortcodes` (shortcodes publics).
- Le text-domain unique est `club-easy-event`.

## Hooks principaux
- `cee_schedule_query_args` – Filtrer les arguments de la requête du shortcode `[cee_schedule]`.
- `cee_event_shortcode_output` – Filtrer le HTML renvoyé par `[cee_event]`.
- `cee_events_bulk_shift_days` – Déclenché après un décalage groupé des dates d’événements (`$post_ids`, `$offset`).
- `cee_roles_manager_allowed_roles` – Restreindre ou étendre la liste des rôles gérés depuis l’écran « Gestion des rôles ».
- `cee_rsvp_updated` – Conserve le comportement existant lors de la mise à jour d’un RSVP.
- `cee_primary_color` – Filtrer la couleur primaire utilisée sur le front-end.

## Scripts & i18n
- Les scripts d’administration utilisent `wp_set_script_translations` :
  - `cee-datetime-enhance` (fallback datepicker + time select).
  - `cee-roles-manager` (recherche filtrée + gestion des cases à cocher).
  - `cee-onboarding`.
- Les chaînes côté PHP doivent être encapsulées dans `__()`, `_e()`, `esc_html__()` selon le contexte.

## Quick edit & actions groupées
- `CEE_Admin::quick_edit_date_field()` injecte le champ `date` dans la modification rapide (`quick_edit_custom_box`).
- `CEE_Admin::handle_bulk_actions()` traite les actions personnalisées `cee_shift_days_forward` et `cee_shift_days_backward`.
- Les scripts JS correspondants utilisent l’API Clipboard et `prompt()` pour saisir l’offset.

## Rôles & sécurité
- `CEE_Roles_Manager` repose sur `WP_User_Query` et vérifie systématiquement les capacités (`manage_options`, `edit_user`).
- Toutes les actions POST sont protégées par nonce (`cee_roles_single_*`, `cee_roles_bulk`).
- Les rôles gérés peuvent être modifiés via le filtre `cee_roles_manager_allowed_roles`.

## Shortcodes
- `[cee_event]` réutilise les classes CSS front existantes (`cee-schedule-*`).
- `[cee_schedule]` et `[cee_roster]` continuent d’appeler `CEE_Frontend::mark_assets_needed()` pour charger les assets uniquement lorsque nécessaire.

## Internationalisation
- Les nouveaux fichiers de langue `.po` et `.pot` couvrent toutes les chaînes ajoutées (FR/EN/ES). Aucun fichier `.mo` n’est versionné.
