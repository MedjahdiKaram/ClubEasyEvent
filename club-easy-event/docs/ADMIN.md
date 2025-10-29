# Guide Administrateur

## Navigation principale
- **Club Easy Event → Événements** : liste et création des événements (`edit.php?post_type=cee_event`).
- **Club Easy Event → Lieux** : gestion complète des infrastructures (`edit.php?post_type=cee_venue`).
- **Club Easy Event → Ajouter un lieu** : accès direct au formulaire de création (`post-new.php?post_type=cee_venue`).
- **Club Easy Event → Équipes** : gestion des effectifs (`edit.php?post_type=cee_team`).
- **Club Easy Event → Joueurs** : création des fiches joueurs (`edit.php?post_type=cee_player`).
- **Club Easy Event → Ligues** : taxonomie `cee_league`.
- **Club Easy Event → Gestion des rôles** : écran réservé aux administrateurs pour attribuer ou retirer les rôles WordPress et `team_manager`.
- **Club Easy Event → Paramètres** : réglages couleurs, rappels e-mail, WooCommerce, notifications et inscription joueurs.

## Tableau de bord du plugin
- Accessible via le menu parent « Club Easy Event » pour les administrateurs et les `team_manager`.
- Les indicateurs principaux affichent le nombre d’équipes, de joueurs, d’événements prévus sur les 7 prochains jours et d’événements en attente d’approbation.
- Une liste des prochains événements synthétise date/heure, adversaires, lieu et état d’approbation (badges identiques aux colonnes d’administration).
- Le bloc **À valider** met en avant les contenus en attente si l’utilisateur possède la capacité `cee_approve_content` (lien direct vers la vue filtrée).
- Les **Actions rapides** proposent des boutons ouvrant dans un nouvel onglet la création d’une équipe, d’un joueur, d’un lieu ou d’un événement.
- La section **Dernières modifications** permet de suivre les changements récents (statut et date de modification).
- Chaque panneau est repliable (bouton « Basculer la section ») pour simplifier la lecture sur mobile.

## Workflow de vérification & approbation
- Une méta-box « Vérification & approbation » est disponible sur les contenus `cee_event`, `cee_team`, `cee_player` et `cee_venue`.
- Les états disponibles sont : **Brouillon**, **En attente**, **Approuvé** et **Rejeté**. Les transitions sont protégées par des capacités (`cee_mark_pending`, `cee_approve_content`, `cee_reject_content`).
- Les boutons « Marquer comme approuvé » et « Rejeter » sont affichés selon vos droits. Lors d’un rejet, ajoutez une note pour informer l’auteur.
- La colonne « Approbation » apparaît sur toutes les listes d’éléments avec un badge coloré. Utilisez les vues rapides (Tous, À valider, Approuvés, Rejetés) ou le filtre déroulant pour trier par état.
- Deux actions groupées « Approuver » et « Rejeter » appliquent un changement massif (nonce et confirmation inclus). Un message de succès récapitule le nombre d’éléments mis à jour.

## Création / édition d’un événement
- La méta-box avancée regroupe les champs en sections : **Planning** (date, heure, type), **Participants & Lieu** (équipes, lieu) et **Résultat**.
- Les validations administratives vérifient les règles métiers : équipe à domicile obligatoire pour les matchs/entraînements, lieu recommandé, impossibilité de sélectionner à la fois une équipe interne et un libellé externe pour l’adversaire.
- Les avertissements sont affichés sous les champs concernés et récapitulés dans un bloc « Points à vérifier » après enregistrement.
- Les aides contextuelles rappellent les formats attendus (date ISO, horaire 24h) et l’impact du type d’événement.
- Le champ **Lieu** liste uniquement les contenus `cee_venue` approuvés et publiés. Si aucun n’est disponible, une notice propose de créer un lieu (lien nouvel onglet) sans bloquer l’enregistrement.

## Assignation joueurs ↔ équipes
- Sur une fiche équipe, la méta-box « Joueurs de l’équipe » propose une liste filtrable et des cases à cocher pour associer rapidement plusieurs joueurs.
- Sur une fiche joueur, la méta-box « Équipes du joueur » permet de cocher les équipes internes concernées. Les relations sont synchronisées automatiquement dans les deux sens (`_cee_team_players` ↔ `_cee_player_teams`).
- Les scripts d’administration offrent une recherche instantanée et une interface accessible au clavier.

## Notifications de mises à jour d’événement
- Activez l’option **Notifications de mise à jour d’événement** dans **Paramètres → Inscription joueurs** pour informer automatiquement les joueurs et managers lorsqu’une date, une heure, un lieu ou une équipe change.
- Les e-mails sont adressés aux utilisateurs liés aux équipes concernées (joueurs et managers). Un mécanisme anti-spam limite l’envoi à un message toutes les 30 minutes par destinataire.
- Personnalisez l’objet et le contenu grâce aux balises `{event_name}`, `{event_date}`, `{event_time}`, `{team_home}`, `{team_away}`, `{venue}`, `{event_link}` et `{changes_list}`.

## Formulaire d’inscription joueurs
- Le shortcode `[cee_player_signup]` affiche un formulaire responsive pour que les nouveaux joueurs soumettent leurs informations (coordonnées, contact d’urgence, note, âge).
- Chaque soumission crée un utilisateur WordPress (rôle par défaut filtrable) et un contenu `cee_player` en statut « draft ». Par défaut les joueurs restent désactivés (`_cee_player_enabled = 0`).
- Sélectionnez l’équipe associée par défaut, le message de confirmation et l’activation automatique depuis **Paramètres → Inscription joueurs**. Des notifications peuvent être envoyées à l’administrateur et au joueur.
- Le formulaire est protégé par nonce, honeypot et limite de fréquence (1 soumission par minute et par e-mail/IP).

## Shortcodes
- **[cee_event id="123"]** : carte événement (date, heure, lieu, lien détail).
- **[cee_schedule team_id="123" season_id="456"]** : calendrier filtré par équipe/saison.
- **[cee_roster team_id="123"]** : liste des joueurs d’une équipe.
- **[cee_player_card id="123"]** : carte rapide d’un joueur (numéro, poste, équipes).
- **[cee_player_signup]** : formulaire d’inscription joueur décrit ci-dessus.
- Chaque liste d’objets (événements, équipes, joueurs) affiche une colonne « Shortcode » avec un bouton de copie.

## Interface publique modernisée
- Les assets `front-modern.css` et `front-modern.js` apportent une mise en page responsive, des cartes harmonisées et des effets légers sur l’ensemble des shortcodes.
- Une alerte s’affiche côté front lorsque l’âge déclaré est inférieur à 18 ans pour rappeler la nécessité d’un consentement parental.

## Actions rapides et validation
- Les actions de modification rapide et les décalages groupés d’événements restent disponibles et compatibles avec les nouveaux champs.
- Les avertissements d’assignation (équipes/lieu) sont stockés temporairement et s’affichent en haut de l’éditeur après enregistrement.

## Gestion des rôles
- Accessible uniquement aux comptes disposant de `manage_options`.
- Recherche par nom ou e-mail (serveur) + filtre instantané dans le tableau.
- Cases par rôle (`team_manager`, `editor`, `author`, `contributor`, `subscriber`, et rôles personnalisés autorisés).
- Bouton « Mettre à jour » par utilisateur + actions groupées pour affecter ou retirer des rôles sur plusieurs comptes.
- Chaque action est sécurisée par nonce et vérification de capacités.
