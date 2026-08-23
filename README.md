# Watchly

> Application web de gestion de collection cinématographique — Projet fil rouge CDA · IPSSI · Session Novembre 2025

[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)](https://php.net)
[![Symfony](https://img.shields.io/badge/Symfony-7-000000?logo=symfony&logoColor=white)](https://symfony.com)
[![React](https://img.shields.io/badge/React-18-61DAFB?logo=react&logoColor=black)](https://react.dev)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)](https://docs.docker.com/compose)
[![CI](https://github.com/fluffy1211/watchly/actions/workflows/ci.yml/badge.svg?branch=develop)](https://github.com/fluffy1211/watchly/actions)

---

## Navigation

- [Présentation](#présentation)
- [Fonctionnalités](#fonctionnalités)
- [Stack technique](#stack-technique)
- [Architecture](#architecture)
- [Base de données](#base-de-données)
- [API — Endpoints](#api--endpoints)
- [Installation](#installation)
- [Variables d'environnement](#variables-denvironnement)
- [Tests](#tests)
- [Structure du projet](#structure-du-projet)
- [CI/CD](#cicd)
- [Sécurité](#sécurité)
- [Documentation](#documentation)

---

## Présentation

**Watchly** est une Single Page Application développée dans le cadre de la formation **CDA (Concepteur Développeur d'Applications)** à l'IPSSI. Développé de janvier à juin 2026, il constitue le projet fil rouge de la session Novembre 2025.

### Contexte

L'offre cinématographique est dispersée sur de multiples plateformes (cinéma, streaming, DVD). Watchly centralise la consommation cinématographique d'un utilisateur : recherche de films via l'API TMDB, gestion d'une watchlist, suivi des films vus, notation personnelle et rédaction d'avis.

### Objectifs fonctionnels

| Acteur | Périmètre |
|--------|-----------|
| **Utilisateur** | Inscription / connexion JWT · Recherche TMDB · Gestion de collection (Watchlist / Vu / Favori) · Notation 1–5 étoiles · Avis textuels |
| **Administrateur** | Consultation et gestion des comptes utilisateurs · Suppression RGPD (droit à l'oubli) |

---

## Fonctionnalités

- **Authentification** : inscription, connexion par token JWT (LexikJWTAuthenticationBundle, clés RSA), déconnexion
- **Recherche de films** : interrogation en temps réel de l'API TMDB par titre, affichage en grille responsive (6 → 3 colonnes)
- **Fiche film** : titre, synopsis, affiche, durée, note TMDB, genres — données enrichies à la volée depuis TMDB
- **Collection personnelle** : trois statuts exclusifs `WATCHLIST` / `WATCHED` / `FAVORITE`, un seul enregistrement par couple (utilisateur, film)
- **Notation** : note personnelle de 1 à 5 étoiles, disponible uniquement pour les films `WATCHED` ou `FAVORITE`
- **Avis** : un seul avis textuel par couple (utilisateur, film), modifiable à tout moment
- **Favoris** : marquer un film vu comme coup de cœur (implique `WATCHED`)
- **Tableau de bord** : filtres par statut et par note, statistiques (films vus, à voir, note moyenne, répartition)
- **Back-office** : gestion des membres, suppression de compte avec cascade (RGPD)
- **Gestion du compte** : changement de mot de passe, suppression de compte (RGPD, confirmation par mot de passe)

---

## Stack technique

### Back-end

| Technologie | Version | Rôle |
|-------------|---------|------|
| PHP | 8.4 | Langage (PSR-4, PSR-12, attributs PHP 8) |
| Symfony | 7.4 | Framework API REST (controllers, services, DI) |
| LexikJWTAuthenticationBundle | 3.2 | Authentification stateless par tokens RS256 |
| Doctrine ORM | 3.6 | Mapping objet-relationnel, migrations |
| Symfony HttpClient | — | Appels HTTPS vers l'API TMDB |
| Symfony Serializer | — | Sérialisation JSON des réponses API |
| Symfony Validator | — | Validation des données entrantes (Assert) |
| Symfony Mailer | 7.4 | Envoi d'emails (mot de passe oublié, SMTP Gmail) |
| NelmioCorsBundle | — | En-têtes CORS pour les requêtes cross-origin React |
| MySQL | 8.4 | Base de données relationnelle (InnoDB) |

### Front-end

| Technologie | Version | Rôle |
|-------------|---------|------|
| React | 18.3.1 | SPA (composants, hooks, context) |
| React Router | 7.16.0 | Routage côté client (react-router-dom) |
| Vite | 5.4.2 | Bundler de développement |
| Axios | 1.16.1 | Client HTTP avec intercepteur JWT global |
| CSS Modules | — | Styles scopés par composant |
| Storybook | 10.5.10 | Documentation des composants et fondations (design tokens) |

### Infra & outillage

| Outil | Rôle |
|-------|------|
| Docker Compose | Orchestration des 3 services (app, db, front) |
| GitHub Actions | Pipeline CI |
| PHPUnit 13.1 | Tests back-end (unitaires + intégration) |

---

## Architecture

### Vue d'ensemble — Architecture n-tiers

```
┌─────────────────────────────────────────────────────────┐
│                    Docker Compose                       │
│                                                         │
│  ┌─────────────────────────────────┐                    │
│  │  Tier 1 — Présentation          │                    │
│  │  React 18 SPA · Vite · Axios    │  :3000             │
│  └────────────┬────────────────────┘                    │
│               │ REST JSON + JWT                         │
│  ┌────────────▼────────────────────┐   HTTPS            │
│  │  Tier 2 — Application           │ ──────► API TMDB   │
│  │  Symfony 7 · Controllers        │                    │
│  │  Services · LexikJWT            │  :8080             │
│  │  Doctrine ORM                   │                    │
│  └────────────┬────────────────────┘                    │
│               │ SQL (PDO)                               │
│  ┌────────────▼────────────────────┐                    │
│  │  Tier 3 — Données               │                    │
│  │  MySQL 8 · Volume persistant    │  :3306             │
│  └─────────────────────────────────┘                    │
└─────────────────────────────────────────────────────────┘
```

### Pattern MVC adapté API headless

| Couche MVC | Implémentation Watchly |
|------------|------------------------|
| **Model** | Entités Doctrine (User, Film, Genre, UserCollection, Review) + Repositories |
| **Controller** | AuthController, FilmController, CollectionController, ReviewController, AdminController |
| **View** | Application React SPA (composants, pages, hooks) — consomme l'API via Axios |

### Services métier

| Service | Responsabilité |
|---------|----------------|
| `TMDBService` | Encapsule les appels HTTPS vers l'API TMDB (search, détails, populaires). Clé API via `.env` |
| `CollectionService` | Logique métier de la collection : statuts exclusifs, favori ↔ vu, note conditionnelle |
| `FilmService` | Pattern find-or-create pour la persistance locale des films TMDB |

### Flux d'authentification JWT

```
React SPA ──POST /api/login──► AuthController
                               └─► UserRepository (findByEmail)
                               └─► password_verify()
                               └─► LexikJWT.createToken()
           ◄── { token: "eyJ..." } ──
```

Le token (payload : `id`, `email`, `roles`, durée 3600s) est inclus dans chaque requête suivante via `Authorization: Bearer {token}`.

---

## Base de données

10 tables relationnelles (MySQL 8 InnoDB), générées via les migrations Doctrine.

### Schéma relationnel

```
utilisateur (id PK, email UNIQUE, password, username UNIQUE, roles JSON,
             created_at, updated_at)

film (id PK, tmdb_id UNIQUE, title, original_title, overview TEXT,
      poster_path, backdrop_path, release_date DATE, runtime INT,
      vote_average DECIMAL(3,1), created_at)

genre (id PK, tmdb_id UNIQUE, name VARCHAR(100))

film_genre (film_id PK FK, genre_id PK FK)          -- pivot ManyToMany

user_collection (id PK, user_id FK, film_id FK,
                 status ENUM('WATCHLIST','WATCHED','FAVORITE'),
                 rating SMALLINT NULL CHECK(1–5),
                 added_at, watched_at NULL,
                 UNIQUE(user_id, film_id))

review (id PK, user_id FK, film_id FK, content TEXT,
        created_at, updated_at,
        UNIQUE(user_id, film_id))

movie_list (id PK, title, description, visibility ENUM('PUBLIC','PRIVATE'),
            created_at, updated_at, owner_id FK)

list_film (id PK, added_at, list_id FK, film_id FK,
           UNIQUE(list_id, film_id))

list_comment (id PK, content, created_at, updated_at, list_id FK, author_id FK)

comment_report (id PK, reason, created_at, comment_id FK, reporter_id FK,
                UNIQUE(comment_id, reporter_id))
```

### Règles de gestion critiques

| Règle | Description |
|-------|-------------|
| RG-03 | `WATCHLIST`, `WATCHED` et `FAVORITE` sont mutuellement exclusifs |
| RG-04 | `FAVORITE` implique `WATCHED` — enforced dans `CollectionService` |
| RG-05 | `rating` uniquement si statut `WATCHED` ou `FAVORITE` |
| RG-06 | Un seul avis par couple (user, film) — contrainte UNIQUE sur `review` |
| RG-07 | Un film TMDB n'est persisté qu'une seule fois (UNIQUE sur `tmdb_id`) |
| RG-08 | Suppression user → cascade sur `user_collection` et `review` |
| RG-09 | Une liste a une visibilité `PUBLIC`/`PRIVATE` ; seul le propriétaire peut la modifier |
| RG-10 | `watched_at` renseigné automatiquement au passage en `WATCHED`/`FAVORITE` |
| RG-11 | Un seul signalement par couple (commentaire, utilisateur) — contrainte UNIQUE sur `comment_report` |

### Stratégie de stockage TMDB

Cache local enrichi : les métadonnées essentielles (titre, synopsis, affiche, genres) sont persistées dès la première interaction utilisateur. Les données détaillées (casting complet, bandes-annonces) sont récupérées à la volée depuis TMDB lors de la consultation de la fiche film.

---

## API — Endpoints

Toutes les routes retournent du JSON. Les routes protégées nécessitent `Authorization: Bearer <token>`.

### Authentification

| Méthode | Route | Accès | Description |
|---------|-------|-------|-------------|
| `POST` | `/api/register` | Public | Inscription (email, password, username) |
| `POST` | `/api/login` | Public | Connexion → retourne le token JWT |
| `POST` | `/api/password-reset/request` | Public | Demande de réinitialisation du mot de passe (email envoyé) |
| `POST` | `/api/password-reset/reset` | Public | Réinitialise le mot de passe avec le token reçu |

### Films

| Méthode | Route | Accès | Description |
|---------|-------|-------|-------------|
| `GET` | `/api/films/search?q={titre}` | Authentifié | Recherche TMDB par titre |
| `GET` | `/api/films/popular` | Authentifié | Films populaires TMDB (paginé) |
| `GET` | `/api/films/genres` | Authentifié | Liste des genres TMDB |
| `GET` | `/api/films/discover` | Authentifié | Découverte de films par genre (paginé) |
| `GET` | `/api/films/{id}` | Authentifié | Détails d'un film (cache local + TMDB) |
| `POST` | `/api/collection/add` | Authentifié | Ajouter un film à la collection |

### Collection

| Méthode | Route | Accès | Description |
|---------|-------|-------|-------------|
| `GET` | `/api/collection` | Authentifié | Récupérer sa collection |
| `DELETE` | `/api/collection/{id}` | Authentifié | Retirer un film de la collection |
| `PATCH` | `/api/collection/{id}/status` | Authentifié | Changer le statut (WATCHLIST / WATCHED / FAVORITE) |
| `PATCH` | `/api/collection/{id}/favorite` | Authentifié | Basculer le statut favori |

### Avis

| Méthode | Route | Accès | Description |
|---------|-------|-------|-------------|
| `PUT` | `/api/films/{id}/review` | Authentifié | Créer ou mettre à jour son avis |
| `GET` | `/api/films/{id}/reviews` | Public | Lister les avis d'un film |

### Profil

| Méthode | Route | Accès | Description |
|---------|-------|-------|-------------|
| `GET` | `/api/profile/{username}` | Public | Consulter un profil (bio, avatar, films vus) |
| `GET` | `/api/profile/me/export` | Authentifié | Exporter ses données personnelles (RGPD) |
| `PUT` | `/api/profile` | Authentifié | Mettre à jour sa bio |
| `POST` | `/api/profile/avatar` | Authentifié | Envoyer un avatar |
| `DELETE` | `/api/profile/avatar` | Authentifié | Supprimer son avatar |
| `PUT` | `/api/profile/password` | Authentifié | Changer son mot de passe (mot de passe actuel requis) |
| `DELETE` | `/api/profile` | Authentifié | Supprimer son propre compte (RGPD — mot de passe requis, cascade) |

### Listes

| Méthode | Route | Accès | Description |
|---------|-------|-------|-------------|
| `POST` | `/api/lists` | Authentifié | Créer une liste (titre, description, visibilité) |
| `GET` | `/api/lists?q={recherche}` | Public | Parcourir/rechercher les listes publiques (par titre ou créateur) |
| `GET` | `/api/lists/{id}` | Public* | Détails d'une liste (403 si privée et non propriétaire) |
| `PATCH` | `/api/lists/{id}` | Propriétaire | Modifier titre / description / visibilité |
| `DELETE` | `/api/lists/{id}` | Propriétaire | Supprimer la liste |
| `POST` | `/api/lists/{id}/films/{tmdbId}` | Propriétaire | Ajouter un film à la liste |
| `DELETE` | `/api/lists/{id}/films/{tmdbId}` | Propriétaire | Retirer un film de la liste |
| `POST` | `/api/lists/{id}/comments` | Authentifié | Commenter une liste (publique, ou privée si propriétaire) |
| `GET` | `/api/lists/{id}/comments` | Public* | Lister les commentaires d'une liste |
| `DELETE` | `/api/comments/{id}` | Auteur du commentaire | Supprimer son propre commentaire |
| `POST` | `/api/comments/{id}/report` | Authentifié | Signaler un commentaire (raison optionnelle) |

\* Sans authentification, seules les listes publiques sont accessibles.

### Administration

| Méthode | Route | Accès | Description |
|---------|-------|-------|-------------|
| `GET` | `/api/admin/users` | `ROLE_ADMIN` | Lister tous les utilisateurs |
| `PATCH` | `/api/admin/users/{id}` | `ROLE_ADMIN` | Modifier un compte |
| `DELETE` | `/api/admin/users/{id}` | `ROLE_ADMIN` | Supprimer un compte (RGPD — cascade) |
| `GET` | `/api/admin/comment-reports` | `ROLE_ADMIN` | Lister les commentaires signalés |
| `PATCH` | `/api/admin/comment-reports/{id}` | `ROLE_ADMIN` | Traiter un signalement (conserver ou supprimer le commentaire) |

---

## Installation

### Prérequis

- Docker Desktop ou OrbStack
- Docker Compose v2+
- Git

### Lancement

```bash
# 1. Cloner le dépôt
git clone https://github.com/fluffy1211/watchly.git
cd watchly

# 2. Copier et configurer les variables d'environnement
cp .env.example .env
# éditer .env (voir section ci-dessous)

# 3. Démarrer tous les services
docker compose up -d --build

# 4. Générer les clés JWT RSA
docker compose exec app php bin/console lexik:jwt:generate-keypair

# 5. Exécuter les migrations Doctrine
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction

# 6. (optionnel) Promouvoir un utilisateur en administrateur
docker compose exec app php bin/console app:promote-user email@example.com
```

L'application est accessible sur :
- **Front-end** : http://localhost:3000
- **API** : http://localhost:8080/api

---

## Variables d'environnement

Copier `.env.example` → `.env` et renseigner les valeurs suivantes :

```dotenv
# Base de données
DATABASE_URL="mysql://watchly:watchly@db:3306/watchly?serverVersion=8.4.0"

# Clé API TMDB (obtenir sur https://developer.themoviedb.org)
TMDB_API_KEY=your_tmdb_api_key_here

# JWT — clés RSA générées via lexik:jwt:generate-keypair
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_passphrase_here
```

> Ne jamais committer `.env` avec des valeurs réelles. Le fichier est listé dans `.gitignore`.

---

## Tests

### Lancer la suite PHPUnit (112 tests, 13 fichiers)

```bash
# Via Docker
docker compose exec app php bin/phpunit

# En local (PHP 8.4 requis)
cd backend
php bin/phpunit
```

### Structure des tests back-end

```
backend/tests/
├── BaseWebTestCase.php             # Client HTTP authentifié réutilisable
├── bootstrap.php
├── Controller/
│   ├── AuthControllerTest.php      # Tests d'intégration endpoints auth
│   ├── FilmControllerTest.php
│   ├── CollectionControllerTest.php
│   ├── ReviewControllerTest.php
│   ├── ListControllerTest.php
│   ├── ListCommentControllerTest.php
│   ├── ProfileControllerTest.php
│   ├── PasswordResetControllerTest.php
│   ├── AdminControllerTest.php
│   └── RateLimitingTest.php
├── Service/
│   ├── CollectionServiceTest.php   # Tests unitaires règles métier (RG-03 à RG-10)
│   └── TMDBServiceTest.php         # Tests unitaires service TMDB (mock HTTP)
└── EventListener/
    └── SecurityHeadersListenerTest.php
```

Objectif de couverture : **> 70 % des classes métier** (exigence formation CDA).

### Tests front-end

16 fichiers de tests **Jest + React Testing Library** :

- 9 modules d'API mockés (`api/*.test.js`)
- `ProtectedRoute`, `Header`
- 4 composants UI (`Avatar`, `StarRating`, `WatchedModal`, `useToast`)
- `AuthContext`

```bash
cd frontend
npm test
```

---

## Structure du projet

```
watchly/
├── docker-compose.yml              # Orchestration des 3 services
├── .env.example                    # Template des variables d'environnement
├── .github/
│   └── workflows/
│       └── ci.yml                  # Pipeline GitHub Actions
│
├── backend/                        # API Symfony 7.4
│   ├── src/
│   │   ├── Controller/             # AuthController, FilmController, CollectionController,
│   │   │                           # ReviewController, AdminController, ListController,
│   │   │                           # ListCommentController, ProfileController, PasswordResetController
│   │   ├── Entity/                 # User, Film, Genre, UserCollection, Review,
│   │   │                           # MovieList, ListFilm, ListComment, CommentReport
│   │   ├── Repository/             # UserRepository, FilmRepository, ...
│   │   ├── Service/                # TMDBService, CollectionService, FilmService, ListService,
│   │   │                           # ListCommentService, CommentReportService, ProfileService,
│   │   │                           # DataExportService, PasswordResetService
│   │   ├── EventListener/          # SecurityHeadersListener, JWTCreatedListener
│   │   └── Command/                # PromoteUserCommand
│   ├── config/
│   │   ├── jwt/                    # Clés RSA (non versionnées)
│   │   └── packages/               # security.yaml, nelmio_cors.yaml, lexik_jwt.yaml
│   ├── migrations/                 # Migrations Doctrine
│   ├── tests/                      # Suite PHPUnit (13 fichiers, 112 tests)
│   └── Dockerfile                  # PHP 8.4-FPM + Nginx
│
└── frontend/                       # SPA React 18
    ├── src/
    │   ├── api/                    # axiosInstance.js, auth.js, films.js,
    │   │                           # collection.js, reviews.js, admin.js
    │   ├── components/
    │   │   ├── layout/             # Header, Layout
    │   │   └── ui/                 # FilmCard, StarRating, Badge, Button,
    │   │                           # Toast, WatchedModal, Spinner
    │   ├── context/
    │   │   └── AuthContext.jsx     # Gestion du token JWT côté client
    │   ├── pages/                  # Landing, Auth, Search, FilmDetail,
    │   │                           # Collection, Admin
    │   └── styles/                 # variables.css, global.css, reset.css
    └── Dockerfile                  # Node 20 build + Nginx alpine
```

---

## CI/CD

### Pipeline CI

Déclenchée automatiquement à chaque push sur `develop` et `main`, ainsi que sur les pull requests vers `main` :

| Job | Étapes |
|-----|--------|
| `backend-tests` | Checkout · Setup PHP 8.4 · `composer install` · migrations Doctrine · `php bin/phpunit` |
| `frontend-build` | Checkout · Setup Node 22 · `npm ci` · ESLint · Jest · `npm run build` |

### Stratégie de branches

| Branche | Rôle |
|---------|------|
| `main` | Version stable — tags de jalons (`v0.5`, `v1.0`) |
| `develop` | Intégration continue — déclenche la CI |
| `feature/*` | Nouvelles fonctionnalités |
| `fix/*` | Corrections de bugs |

---

## Sécurité

### Authentification & autorisation

- **JWT RS256** : tokens signés avec une paire de clés RSA asymétriques (config/jwt/). Durée de validité : 3600 secondes.
- **Rôles** : `ROLE_USER` (utilisateurs authentifiés) et `ROLE_ADMIN` (back-office). Firewall Symfony `api` configuré dans `security.yaml`.
- **Hachage** : mots de passe hachés via `UserPasswordHasherInterface` (Bcrypt).

### Protection OWASP Top 10

| Menace | Mitigation |
|--------|-----------|
| Injection SQL | Doctrine ORM — requêtes paramétrées, aucune concaténation SQL brute |
| XSS | Échappement natif React (DOM virtuel) |
| CSRF | Architecture stateless JWT — pas de session côté serveur |
| Broken Access Control | Vérification JWT sur chaque route protégée ; `ROLE_ADMIN` requis pour le back-office |
| Security Misconfiguration | Secrets dans `.env` (non versionné), CORS restreint via NelmioCorsBundle |
| Sensitive Data Exposure | Mots de passe jamais retournés dans les réponses API |

### RGPD

- Droit à l'oubli : suppression par l'utilisateur lui-même (`DELETE /api/profile`, confirmation par mot de passe) ou par un administrateur (`DELETE /api/admin/users/{id}`) — cascade SQL sur `user_collection` et `review` dans les deux cas
- Consentement explicite affiché à l'inscription
- Données personnelles limitées (email, username, password hashé)

---

## Design system

| Token | Valeur | Usage |
|-------|--------|-------|
| Fond principal | `#0D0D0F` | Background général |
| Surface / Card | `#18181E` | Cartes films, modales |
| Accent or | `#E8B86D` | CTA, étoiles, badge actif |
| Texte principal | `#F2F0ED` | Titres |
| Texte secondaire | `#8B8B9A` | Métadonnées, dates |
| Succès | `#4ADE80` | Badge "Film vu" |
| Danger | `#F87171` | Suppressions, erreurs |

Polices : **Plus Jakarta Sans** (titres et corps) · **IBM Plex Mono** (données, badges 12–14px)

Documentation : **Storybook** (`npm run storybook`) — 8 composants (Button, Avatar, FilmCard, ListCard, Spinner, StarRating, Toast, WatchedModal) et 5 pages de fondations/tokens (couleurs, élévation, radius, spacing, typographie).

---

## Documentation

Livrables produits dans le cadre de la formation CDA IPSSI, disponibles dans [`docs/jalons/`](docs/jalons/) :

| Titre | Document |
|-------|----------|
| Cahier des Charges Fonctionnel | [Jalon1_CDCF.pdf](docs/jalons/Jalon1_CDCF.pdf) |
| Méthodologie & Conception UX/UI | [Jalon2_CDC.pdf](docs/jalons/Jalon2_CDC.pdf) |
| Modélisation de la Base de Données (MCD/MLD/MPD) | [Jalon3_Modelisation_BDD.pdf](docs/jalons/Jalon3_Modelisation_BDD.pdf) |
| Conception de l'application & Architecture UML | [Jalon4_Conception_Architecture.pdf](docs/jalons/Jalon4_Conception_Architecture.pdf) |

---

*Gabriel Martin — CDA · IPSSI · Session Novembre 2025 · Projet fil rouge janvier → juin 2026*
