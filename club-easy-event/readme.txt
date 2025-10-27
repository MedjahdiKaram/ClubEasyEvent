=== Club Easy Event ===
Contributors: your-name
Tags: events, teams, clubs, schedule, roster, calendar, rsvp, sports, woocommerce
Requires at least: 5.7
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Club Easy Event est la solution WordPress tout-en-un pour planifier vos matchs, gérer vos équipes et partager les résultats avec vos supporters.

== Description ==
Organisez le calendrier sportif de votre club sans friction. Club Easy Event centralise les matchs, entraînements, équipes, joueurs, lieux et inscriptions RSVP directement dans votre site WordPress. Le plugin a été conçu pour les associations et clubs locaux qui veulent une interface claire, des workflows rapides et des shortcodes prêts à l’emploi pour afficher calendriers, effectifs et fiches d’événements.

**Pourquoi choisir Club Easy Event ?**

* Interface administrateur simplifiée pour gérer événements, équipes, joueurs et lieux depuis un même tableau de bord.
* Création rapide d’événements avec sélection de date/heure assistée par calendrier et prise en charge des navigateurs anciens.
* Colonne « Shortcode » dans les listes d’événements et d’équipes pour intégrer instantanément un calendrier ou un effectif sur vos pages publiques.
* Gestion des rôles repensée (administrateurs) avec recherche, filtres et actions groupées pour attribuer les permissions adéquates à vos bénévoles.
* Actions rapides : modifier une date en « Modification rapide » ou décaler plusieurs événements d’un seul coup (+/- N jours).
* Compatible WooCommerce pour monétiser vos saisons et compatible WPML/Polylang pour la traduction.
* Internationalisation incluse (Français, Anglais, Espagnol) et fichiers .pot/.po prêts pour vos propres traductions.

== Features ==
* **Calendrier d’événements sportif** : planifiez les rencontres, entraînements et stages avec lieu, scores, RSVP et rappels e-mail.
* **Gestion des lieux** : menu dédié pour créer, éditer et organiser vos infrastructures rapidement.
* **Gestion des rôles** : écran réservé aux administrateurs pour affecter/désaffecter les rôles `team_manager`, `editor`, `author`, `contributor`, `subscriber` et vos rôles personnalisés.
* **Shortcodes prêts à coller** : `[cee_event id="123"]`, `[cee_schedule team_id="123" season_id="456"]`, `[cee_roster team_id="123"]`.
* **Quick Edit et actions groupées** : modifiez un jour en un clic ou appliquez un décalage de dates sur plusieurs événements sélectionnés.
* **Onboarding guidé** : liens contextuels ouvrant en nouvel onglet pour accéder immédiatement aux écrans essentiels (équipes, joueurs, lieux, paramètres, événements).
* **Compatibilité WooCommerce** : associez vos saisons à des produits pour vendre des abonnements ou cotisations.
* **Hooks & API** : filtres et actions pour développeurs afin d’étendre calendriers, colonnes, notifications ou flux front-end.

== Shortcodes ==
* `[cee_event id="123"]` – Affiche une carte événement responsive avec date, heure, lieu, match-up et lien vers le détail.
* `[cee_schedule team_id="123" season_id="456"]` – Publie le calendrier d’une équipe (option `season_id` facultative).
* `[cee_roster team_id="123"]` – Liste l’effectif d’une équipe avec photos, numéros et positions.

Insérez-les dans vos pages, articles, widgets HTML ou constructeurs compatibles.

== Screenshots ==
1. Tableau de bord Club Easy Event avec onboarding pas-à-pas.
2. Liste d’événements avec colonne « Shortcode », actions rapides et filtres.
3. Ecran « Gestion des rôles » pour affecter plusieurs profils en quelques clics.
4. Carte d’événement générée par le shortcode `[cee_event]`.

== Installation ==
1. Téléversez le dossier du plugin dans `wp-content/plugins/` ou installez-le via « Ajouter une extension ».
2. Activez **Club Easy Event** dans le menu Extensions.
3. Rendez-vous dans **Club Easy Event → Paramètres** pour personnaliser couleurs, rappels et options WooCommerce.
4. Créez vos équipes, joueurs, lieux, puis planifiez les événements.
5. Intégrez vos calendriers et effectifs sur le site public grâce aux shortcodes.

== Frequently Asked Questions ==
= Puis-je utiliser le plugin sur un site multilingue ? =
Oui. Club Easy Event charge les traductions via `load_textdomain()` et fonctionne avec WPML/Polylang. Fournissez vos `.po` supplémentaires si nécessaire.

= Est-ce compatible avec WooCommerce ? =
Oui. Associez une saison à un produit WooCommerce pour vendre des abonnements ou créer des rappels de paiement.

= Puis-je personnaliser les shortcodes ? =
Les shortcodes utilisent des hooks (`apply_filters`) pour ajuster les requêtes ou le rendu. Consultez la documentation développeur pour les exemples.

= Comment limiter l’accès aux écrans d’administration ? =
L’écran « Gestion des rôles » est réservé aux administrateurs (`manage_options`). Vous pouvez toutefois filtrer les rôles disponibles via `cee_roles_manager_allowed_roles`.

== Changelog ==
= 1.1.0 =
* Nouveau menu « Lieux » et accès rapide « Ajouter un lieu » sous Club Easy Event.
* Saisie de dates/horaires enrichie (fallback calendrier + sélecteur 15 min) dans les événements.
* Écran « Gestion des rôles » avec recherche, filtres et actions groupées.
* Colonne « Shortcode » avec bouton de copie pour événements et équipes.
* Quick Edit date et actions groupées pour décaler les événements sélectionnés.
* Shortcode `[cee_event]` pour afficher une fiche événement autonome.
* Onboarding : liens d’action ouverts dans un nouvel onglet.
* Mise à jour des traductions (FR/EN/ES), documentation et fichier .pot.

= 1.0.0 =
* Première version publique.

== Upgrade Notice ==
= 1.1.0 =
Ajoute la gestion des rôles, des lieux, un champ date/heure amélioré, un shortcode d’événement et des actions groupées. Mettez à jour pour profiter des nouveaux outils d’administration.
