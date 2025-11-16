# LiftUp – Plateforme collaborative de déménagements

LiftUp est une application PHP/MySQL développée dans le cadre du module **MO Conception de sites web – semestre 7**. Elle permet de mettre en relation des clients qui organisent leur déménagement et des déménageurs particuliers qui proposent leurs services, conformément au sujet officiel _« Plate-forme de déménagements »_ (`sujet (1).pdf`).

## Fonctionnalités clés

- **Visiteurs** : consultation d’une page d’accueil dynamique (`index.php`), aperçu d’ annonces récentes, présentation de la plateforme (`pages/a_propos.php`, `pages/qui_sommes_nous.php`), accès à l’inscription/connexion.
- **Clients (role = 1)** :
  - Création complète d’une annonce (`pages/creer_demenagement.php`) avec photos, caractéristiques des logements, volumes, dates, nombre de déménageurs, etc.
  - Tableau de bord personnalisé (`pages/tableau_bord.php`) pour suivre ses demandes, modifier/supprimer tant qu’aucune proposition n’est acceptée, marquer un déménagement comme terminé (`pages/tt_valider_demenagement.php`), puis noter les intervenants (`pages/noter_demenageurs.php`, `pages/tt_avis.php`).
  - Gestion des propositions reçues (`pages/propositions.php`) : acceptation/refus, négociation par contre-offres (`pages/tt_contre_offre.php`, `pages/tt_repondre_contre_offre.php`), messagerie privée (`pages/messagerie.php`).
- **Déménageurs (role = 2)** :
  - Parcours des annonces filtrées par statut (`pages/annonces.php`, `pages/detail_annonce.php`) et soumission de propositions (`pages/tt_proposition.php`).
  - Suivi des interventions (en cours, terminées, en attente) via `pages/mes_interventions.php`, messagerie sécurisée, consultation des avis reçus sur le tableau de bord.
- **Administrateurs (role = 3)** :
  - Supervision des comptes (`pages/admin_comptes.php`, `pages/admin_activer_compte.php`, `pages/admin_desactiver_compte.php`).
  - Modération des annonces (`pages/admin_annonces.php`, `pages/admin_supprimer_annonce.php`).
- **Infrastructure applicative commune** :
  - Barre de navigation avec notifications (messages non lus, propositions en attente, acceptations) gérée dans `includes/menu.inc.php`.
  - Système de messages Flash (`includes/message.inc.php`), en-têtes/pieds mutualisés (`includes/header.inc.php`, `includes/footer.inc.php`), styles personnalisés (`assets/css/style.css`) reposant sur Bootstrap 5.3.8 et Google Fonts.

## Structure du projet

```
├── assets/               # Styles et images (dont background-home.png, logos…)
├── auth/                 # Interfaces et contrôleurs d'inscription/connexion/mot de passe
├── config/param.inc.php  # Paramètres globaux (timezone, BASE_PATH, accès MySQL)
├── includes/             # Header/Footer/Menu/gestion des messages
├── pages/                # Ensemble des pages métiers (annonces, messagerie, admin…)
├── sql/                  # Scripts SQL : tables, seed d’exemple, exports
├── index.php             # Page d’accueil avec annonces récentes
└── sujet (1).pdf         # Cahier des charges officiel du module
```

Les traitements côté serveur suivent une convention explicite : chaque formulaire métier (`pages/*.php`) est associé à un contrôleur `pages/tt_*.php` (ou `auth/tt_*.php`) qui valide les entrées, sécurise les requêtes (prepared statements `mysqli`), gère les messages utilisateur et redirige.

## Prérequis

- PHP 8.1+ avec l’extension `mysqli`.
- Serveur web Apache ou équivalent (le projet a été développé/testé avec XAMPP).
- MySQL/MariaDB 10.x.
- Accès internet pour les ressources CDN (Bootstrap, Bootstrap Icons, Google Fonts) ou intégration locale équivalente.

## Installation rapide

1. **Cloner ou copier** le projet dans la racine du serveur web (ex. `htdocs/Projet-conception-site-web` sous XAMPP).
2. **Créer la base de données** (par défaut `bdd`). Importer `sql/exemple.sql` pour générer toutes les tables (`compte`, `demenagement`, `proposition`, `demenagement_image`, `message`, `contre_offre`, `avis`, etc.) et disposer de données de démonstration.
3. **Configurer l’accès MySQL** dans `config/param.inc.php` (`$host`, `$login`, `$passwd`, `$dbname`). Adapter éventuellement la constante `BASE_PATH` à l’URL d’hébergement.
4. **Démarrer Apache + MySQL**, puis ouvrir `http://localhost/Projet-conception-site-web/`.

> Comptes de test fournis par `sql/exemple.sql` (mot de passe `password123`) :  
> - Clients : `marie.dupont@email.com`, `pierre.martin@email.com`  
> - Déménageurs : `luc.moreau@email.com`, `jean.dubois@email.com`

## Utilisation type

1. Un **client** crée une annonce détaillée, éventuellement illustrée (`pages/tt_creer_demenagement.php` gère aussi l’upload des photos dans `assets/images/demenagements/`).
2. Les **déménageurs** consultent `pages/annonces.php`, soumettent une proposition chiffrée et un commentaire justificatif.
3. Le **client** compare les offres, négocie via contre-offres, accepte celles retenues ; un enregistrement est ajouté dans `demenagement_demenageur` pour suivre les intervenants confirmés.
4. Les deux parties échangent via `pages/messagerie.php` (messages persistés dans la table `message`, notifications dans la navbar).
5. Le client valide le déménagement terminé, puis laisse une note/commentaire dans `avis`, visible ensuite sur le tableau de bord du déménageur.
6. L’**administrateur** peut intervenir à tout moment pour modérer les annonces ou comptes.

## Base de données & scripts

- Chaque table possède son script dédié dans `sql/` (`compte.sql`, `demenagements.sql`, `contre_offres.sql`, `messages.sql`, `avis.sql`) afin de faciliter la maintenance ou la réinstallation partielle.
- `sql/exemple.sql` regroupe :
  - Création de l’intégralité du schéma.
  - Insertion de comptes/déménagements/données de test.
  - Requêtes de vérification et rappel des identifiants de test.
- Toutes les contraintes d’intégrité (clés étrangères, cascade delete) respectent le modèle relationnel décrit dans le sujet.

## Sécurité et bonnes pratiques implémentées

- Sessions sécurisées et vérification systématique du rôle avant d’accéder aux pages sensibles (ex. `pages/mes_demenagements.php`, `pages/admin_*`).
- Utilisation de `password_hash`/`password_verify` (`auth/tt_inscription.php`, `auth/tt_connexion.php`).
- Requêtes préparées avec `bind_param` sur les opérations sensibles (création d’annonce, propositions, messagerie, avis…).
- Nettoyage basique des champs via `htmlentities` + échappement `htmlspecialchars` à l’affichage pour limiter les XSS.
- Téléversement d’images limité par extension, taille et dossier dédié.

## Rappel du sujet officiel

Le document `sujet (1).pdf` précise :

- **Contexte** : proposer une plateforme où des clients décrivent précisément leur déménagement (date, villes, type de logements, volume, images, nombre de déménageurs) et où des déménageurs particuliers soumettent leurs offres.
- **Rôles utilisateurs** : visiteur (lecture + inscription), client (publication/gestion d’annonces, choix des déménageurs, Q&A, notation), déménageur (consultation, propositions, demande d’informations), administrateur (gestion des annonces et comptes).
- **Livrables** : conception (arborescence, maquettes desktop/mobile, description des pages, modèle de données, répartition des tâches), réalisation (site fonctionnel, accessible, adaptatif via Bootstrap, conforme au dossier, dynamique via la BDD) et script de création de la base.
- **Déploiement & soutenance** : hébergement sur le serveur de l’école, base remplie avec des utilisateurs de chaque catégorie, tests réguliers.
- **Critères d’évaluation** : conformité au dossier, cohérence/navigation, adaptabilité mobile/desktop, validation W3C, sécurité (anti injections SQL/XSS, protection des pages).

Ce README synthétise comment le code présent répond à ces attentes et où retrouver chaque fonctionnalité.

## Aller plus loin

- Compléter les maquettes/documents de conception et les placer dans un dossier `docs/` pour une trace historique.
- Ajouter des tests automatisés (PHPUnit, tests end-to-end) et des workflows CI pour vérifier la qualité du code.
- Centraliser la configuration sensible via des variables d’environnement (ex. `dotenv`), renforcer les contrôles d’upload (compression, redimensionnement), internationaliser l’interface.
- Préparer un script de déploiement (rsync/FTP) afin de suivre la préconisation du sujet concernant l’hébergement sur le serveur de l’école.

Bonne exploration !
