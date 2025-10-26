=== Club Easy Event ===
Contributors: 
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Le plugin le plus simple pour gérer les événements, les équipes et les membres de votre club sportif directement dans WordPress.

== Description ==
Club Easy Event fournit une solution intuitive pour organiser les matchs, gérer les équipes, suivre la présence des joueurs et centraliser les informations de vos lieux. Pensé pour les bénévoles non techniciens, le plugin ajoute des contenus dédiés (événements, équipes, joueurs, lieux), des taxonomies pour vos saisons et ligues, des méta-données personnalisées et deux shortcodes clés: planning des rencontres et effectif d'équipe.

* Calendrier clair des matchs avec scores, lieux et boutons RSVP.
* Gestion des équipes, joueurs et rôles "manager d’équipe".
* Termes de saison avec intégration WooCommerce optionnelle pour vérifier les cotisations.
* Rappels quotidiens par e-mail automatisés.
* Modèle de couleur personnalisable pour harmoniser vos pages publiques.

== Installation ==
1. Téléversez le dossier `club-easy-event` dans `/wp-content/plugins/`.
2. Activez l’extension via le menu « Extensions » de WordPress.
3. Rendez-vous dans **Club Easy Event → Paramètres** pour personnaliser le modèle d’e-mail et la couleur principale.
4. Créez vos équipes, joueurs, lieux puis vos événements.

== Shortcodes ==
* `[cee_schedule team_id="123" season_id="456"]` — Affiche les événements d’une équipe (filtrage optionnel par saison). Les joueurs connectés assignés à l’équipe peuvent répondre Présent/Absent/Incertain.
* `[cee_roster team_id="123" season_id="456"]` — Liste les joueurs d’une équipe, leurs numéros, postes et l’indication de cotisation WooCommerce si applicable.

== Changelog ==
= 1.0.0 =
* Version initiale avec gestion des contenus Club Easy Event, shortcodes planning/effectif, AJAX RSVP, rappels e-mail quotidiens et intégration WooCommerce optionnelle.

== Frequently Asked Questions ==
= Les RSVP fonctionnent-ils pour les visiteurs ? =
Seuls les utilisateurs connectés liés à un joueur peuvent enregistrer une réponse. Les autres visiteurs voient simplement le planning.

= Comment personnaliser le modèle d’e-mail ? =
Allez dans **Club Easy Event → Paramètres** et utilisez les balises disponibles {event_name}, {event_date}, {event_time}, {event_link}, {team_name}, {venue}, {user_name}.
