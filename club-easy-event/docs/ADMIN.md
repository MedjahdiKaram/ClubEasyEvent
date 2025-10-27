# Guide Administrateur

## Navigation principale
- **Club Easy Event → Événements** : liste et création des événements (`edit.php?post_type=cee_event`).
- **Club Easy Event → Lieux** : gestion complète des infrastructures (`edit.php?post_type=cee_venue`).
- **Club Easy Event → Ajouter un lieu** : accès direct au formulaire de création (`post-new.php?post_type=cee_venue`).
- **Club Easy Event → Équipes** : gestion des effectifs (`edit.php?post_type=cee_team`).
- **Club Easy Event → Joueurs** : création des fiches joueurs (`edit.php?post_type=cee_player`).
- **Club Easy Event → Ligues** : taxonomie `cee_league`.
- **Club Easy Event → Gestion des rôles** : écran réservé aux administrateurs pour attribuer ou retirer les rôles WordPress et `team_manager`.
- **Club Easy Event → Paramètres** : réglages couleurs, rappels e-mail, WooCommerce.

## Création / édition d’un événement
- **Date & heure** : champs HTML5 `date` et `time` avec aide visuelle. Un script ajoute automatiquement un calendrier et un sélecteur 15 min sur les navigateurs anciens.
- **Type d’événement** : liste déroulante configurable.
- **Équipes** : sélection des équipes à domicile et adverses, avec possibilité d’indiquer une équipe externe manuellement.
- **Lieu** : sélection d’un lieu enregistré via le nouveau menu Lieux.
- **Scores** : champs numériques facultatifs.

## Actions rapides
- **Modification rapide** : dans la liste des événements, un champ date apparaît pour ajuster le jour sans ouvrir l’éditeur complet.
- **Actions groupées** : deux actions personnalisées permettent de décaler les événements sélectionnés de +N ou −N jours. Lors de l’exécution, saisissez le nombre de jours dans la boîte de dialogue.

## Gestion des rôles
- Accessible uniquement aux comptes disposant de `manage_options`.
- Recherche par nom ou e-mail (serveur) + filtre instantané dans le tableau.
- Checkboxes par rôle (`team_manager`, `editor`, `author`, `contributor`, `subscriber`, et rôles personnalisés éditables).
- Bouton « Mettre à jour » par utilisateur + actions groupées pour affecter ou retirer des rôles sur plusieurs comptes.
- Chaque action est sécurisée par nonce et vérification de capacités.

## Shortcodes
- **[cee_event id="123"]** : carte événement (date, heure, lieu, bouton vers le détail).
- **[cee_schedule team_id="123" season_id="456"]** : calendrier filtré par équipe et saison.
- **[cee_roster team_id="123"]** : liste des joueurs d’une équipe.
- Une colonne « Shortcode » avec bouton « Copier » est affichée dans les listes d’événements et d’équipes pour récupérer rapidement ces codes.

## RSVP & rappels
- Les boutons RSVP (Présent / Incertain / Absent) restent inchangés côté front.
- Les rappels quotidiens s’appuient sur la planification cron existante.
