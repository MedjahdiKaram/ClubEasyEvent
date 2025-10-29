=== Club Easy Event ===
Contributors: your-name
Tags: sports club, events, teams, roster, schedule, approval, notifications, signup, woocommerce, dashboard, venue approval, manager view
Requires at least: 5.7
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Club Easy Event est la solution WordPress tout-en-un pour planifier vos matchs, gérer vos équipes et partager les résultats avec vos supporters.

== Description ==
Organisez le calendrier sportif de votre club sans friction. Club Easy Event centralise les matchs, entraînements, équipes, joueurs, lieux et inscriptions RSVP directement dans votre site WordPress. Le plugin a été conçu pour les associations et clubs locaux qui veulent une interface claire, des workflows rapides, une gestion des approbations, des notifications e-mail automatiques et des shortcodes prêts à l’emploi pour afficher calendriers, effectifs, formulaires d’inscription et fiches d’événements.

**Pourquoi choisir Club Easy Event ?**

* Workflow de validation/approbation complet avec badges, filtres et actions groupées.
* Nouveau tableau de bord administrateur avec indicateurs, prochains événements, validations en attente et actions rapides repliables.
* Formulaire d’inscription joueurs moderne (shortcode) qui crée automatiquement l’utilisateur et la fiche joueur.
* Notifications e-mail lorsque les événements changent (date, heure, lieu, équipes) avec modèle personnalisable.
* Gestion fine des équipes, joueurs, ligues et lieux depuis un tableau de bord unifié.
* Shortcodes responsive (calendrier, effectif, carte joueur, formulaire) et UI front modernisée.
* Intégration WooCommerce pour relier vos événements/saisons à des produits.
* Internationalisation native (FR/EN/ES) compatible WPML/Polylang, SEO-friendly et adaptée aux thèmes modernes (Gutenberg, Astra…).
* Sélection automatique des lieux « approuvés » lors de la création d’un événement (notice si aucun n’est disponible).

== Features ==
* **Calendrier d’événements** : planifiez rencontres, entraînements et stages avec lieu, scores, RSVP et rappels.
* **Workflow d’approbation** : attribuez un état (brouillon, en attente, approuvé, rejeté), laissez une note et contrôlez les transitions via des capacités dédiées.
* **Colonnes & filtres** : colonne « Approbation » sur tous les CPT, vues rapides, filtres déroulants et actions groupées approuver/rejeter.
* **Validations intelligentes** : avertissements automatiques lorsque l’équipe à domicile manque, que le lieu est absent ou que l’adversaire est renseigné deux fois.
* **Assignation joueurs ↔ équipes** : listes filtrables avec cases à cocher, synchronisation bidirectionnelle des métas.
* **Notifications événement** : détection de diff, destinataires automatiques (joueurs + managers), throttling anti-spam, filtres d’extension.
* **Front-end modernisé** : nouveau thème CSS/JS responsive, alertes mineurs, copies clipboard, focus accessibles.
* **Internationalisation** : fichiers `.pot`/`.po` FR, EN, ES prêts à l’emploi ; scripts traduits via `wp_set_script_translations()`.

== Onboarding ==
Le tableau de bord affiche un didacticiel pas-à-pas pour créer vos premières équipes, joueurs, ligues et événements. Les liens d’action ouvrent les écrans utiles dans un nouvel onglet afin de configurer rapidement votre club.

== Approval Workflow ==
Chaque événement, équipe, joueur ou lieu dispose d’une méta-box « Vérification & approbation ». Choisissez l’état, ajoutez une note pour l’auteur, déclenchez l’approbation ou le rejet selon vos droits (`cee_approve_content`, `cee_mark_pending`, `cee_reject_content`). Les listes affichent des badges colorés, un filtre déroulant et des actions groupées sécurisées par nonce.

== Assignments ==
Les méta-boxes « Joueurs de l’équipe » et « Équipes du joueur » proposent une recherche instantanée et synchronisent automatiquement les métas `_cee_team_players` et `_cee_player_teams`. Un hook (`cee_player_team_assignment_changed`) vous permet de déclencher vos automatisations.

== Shortcodes ==
* `[cee_event id="123"]` – carte événement responsive (date, heure, lieu, adversaire, lien détail).
* `[cee_schedule team_id="123" season_id="456"]` – calendrier filtré par équipe et saison.
* `[cee_roster team_id="123"]` – liste l’effectif complet d’une équipe.
* `[cee_player_card id="123"]` – carte joueur individuelle.
* `[cee_player_signup]` – formulaire d’inscription joueur (création user + CPT, assignation équipe par défaut, confirmation personnalisable).

== Settings ==
* **Couleurs & WooCommerce** : personnalisez la couleur primaire, les rappels et l’intégration produits.
* **Notifications** : activez l’envoi lors des modifications d’événements, ajustez l’objet et le modèle avec balises `{event_name}`, `{event_date}`, `{event_time}`, `{team_home}`, `{team_away}`, `{venue}`, `{event_link}`, `{changes_list}`.
* **Inscription joueurs** : choisissez l’équipe par défaut, le message de succès (`{first_name}`), l’activation automatique ou non et les destinataires des alertes.

== Screenshots ==
1. Tableau de bord Club Easy Event avec onboarding pas-à-pas.
2. Liste d’événements avec colonne « Approbation », filtre et actions groupées.
3. Méta-box avancée d’un événement (Planning, Participants & Lieu, Résultat) avec messages de validation.
4. Formulaire `[cee_player_signup]` moderne, responsive et compatible mobile.

== Installation ==
1. Téléversez le dossier du plugin dans `wp-content/plugins/` ou installez-le via « Ajouter une extension ».
2. Activez **Club Easy Event** dans le menu Extensions.
3. Rendez-vous dans **Club Easy Event → Paramètres** pour personnaliser couleurs, rappels, workflow et inscription joueurs.
4. Créez vos équipes, joueurs, lieux, puis planifiez les événements.
5. Intégrez vos calendriers, effectifs, cartes joueurs et formulaire d’inscription sur le site public grâce aux shortcodes.

== Frequently Asked Questions ==
= Comment fonctionne le workflow d’approbation ? =
Chaque contenu possède un état d’approbation stocké en post meta. Selon vos capacités (`cee_approve_content`, `cee_mark_pending`, `cee_reject_content`), vous pouvez changer l’état, ajouter une note et utiliser les actions groupées. Les développeurs peuvent filtrer les autorisations via `cee_can_approve`.

= Puis-je personnaliser les e-mails de notification ? =
Oui, configurez l’objet et le modèle dans **Paramètres → Inscription joueurs**. Les balises `{event_name}`, `{event_date}`, `{event_time}`, `{team_home}`, `{team_away}`, `{venue}`, `{event_link}` et `{changes_list}` sont remplacées automatiquement.

= Le formulaire d’inscription crée-t-il un utilisateur WordPress ? =
Oui, un compte est créé (rôle filtrable) ainsi qu’un contenu `cee_player` en statut « draft ». Le joueur est désactivé tant qu’un responsable ne l’active pas, sauf si l’option d’activation automatique est cochée.

= Le plugin est-il compatible multilingue ? =
Oui. Club Easy Event charge les traductions FR/EN/ES et fonctionne avec WPML/Polylang. Ajoutez vos propres `.po` si nécessaire.

= Est-ce compatible avec WooCommerce ? =
Associez une saison ou un événement à un produit WooCommerce pour vendre des abonnements ou billets. L’intégration reste optionnelle.

== Changelog ==
= 1.3.0 =
* Tableau de bord admin synthétique avec cartes KPI, prochains événements, validations en attente, actions rapides et historiques.
* Sélecteur de lieu filtré sur les contenus approuvés + notice de création rapide lorsque la liste est vide.
* Mise à jour des capacités du rôle `team_manager` (édition/suppression des équipes publiées, privées et approuvées).
* Traductions FR/EN/ES, documentation et fichiers `.pot/.po` synchronisés.

= 1.2.0 =
* Workflow d’approbation complet (états, filtres, actions groupées, badges et notes).
* Méta-box événement modernisée avec validations et regroupement des champs.
* Synchronisation joueurs ↔ équipes avec interfaces de recherche et hooks.
* Notifications e-mail de mise à jour d’événement (diff automatique, throttling, modèles personnalisables).
* Shortcode `[cee_player_signup]`, création automatique d’utilisateur/joueur, messages configurables.
* Modernisation front-end (CSS/JS responsive), alertes mineurs, colonnes shortcodes étendues.
* Traductions FR/EN/ES et documentation mises à jour.

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
= 1.3.0 =
Nouveau tableau de bord administrateur, sélecteur de lieux approuvés et renforcement des capacités `team_manager`. Mettez à jour pour garder une interface fluide et sécurisée.
