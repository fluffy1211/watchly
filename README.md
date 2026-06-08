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

---

## Présentation

**Watchly** est une Single Page Application développée dans le cadre de la formation **CDA (Concepteur Développeur d'Applications)** à l'IPSSI. Le projet couvre 6 jalons mensuels (janvier → juin 2026) et constitue le projet fil rouge de la session Novembre 2025.

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

---

## Stack technique

### Back-end

| Technologie | Version | Rôle |
|-------------|---------|------|
| PHP | 8.4 | Langage (PSR-4, PSR-12, attributs PHP 8) |
| Symfony | 7 | Framework API REST (controllers, services, DI) |
| LexikJWTAuthenticationBundle | — | Authentification stateless par tokens RS256 |
| Doctrine ORM | — | Mapping objet-relationnel, migrations |
| Symfony HttpClient | — | Appels HTTPS vers l'API TMDB |
| Symfony Serializer | — | Sérialisation JSON des réponses API |
| Symfony Validator | — | Validation des données entrantes (Assert) |
| NelmioCorsBundle | — | En-têtes CORS pour les requêtes cross-origin React |
| MySQL | 8 | Base de données relationnelle (InnoDB) |

### Front-end

| Technologie | Version | Rôle |
|-------------|---------|------|
| React | 18 | SPA (composants, hooks, context) |
| Vite | — | Bundler de développement |
| Axios | — | Client HTTP avec intercepteur JWT global |
| CSS Modules | — | Styles scopés par composant |

### Infra & outillage

| Outil | Rôle |
|-------|------|
| Docker Compose | Orchestration des 3 services (app, db, front) |
| GitHub Actions | Pipeline CI/CD |
| PHPUnit | Tests back-end (unitaires + intégration) |
| PHP CS Fixer | Linting PSR-12 |

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

6 tables relationnelles (MySQL 8 InnoDB), générées via les migrations Doctrine.

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
| RG-10 | `watched_at` renseigné automatiquement au passage en `WATCHED`/`FAVORITE` |

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

### Films

| Méthode | Route | Accès | Description |
|---------|-------|-------|-------------|
| `GET` | `/api/films/search?q={titre}` | Authentifié | Recherche TMDB par titre |
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
| `GET` | `/api/films/{id}/reviews` | Authentifié | Lister les avis d'un film |

### Administration

| Méthode | Route | Accès | Description |
|---------|-------|-------|-------------|
| `GET` | `/api/admin/users` | `ROLE_ADMIN` | Lister tous les utilisateurs |
| `PATCH` | `/api/admin/users/{id}` | `ROLE_ADMIN` | Modifier un compte |
| `DELETE` | `/api/admin/users/{id}` | `ROLE_ADMIN` | Supprimer un compte (RGPD — cascade) |

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
DATABASE_URL="mysql://watchly:watchly@db:3306/watchly?serverVersion=8.0"

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

### Lancer la suite PHPUnit (55 tests)

```bash
# Via Docker
docker compose exec app php bin/phpunit

# En local (PHP 8.4 requis)
cd backend
php bin/phpunit
```

### Structure des tests

```
backend/tests/
├── BaseWebTestCase.php             # Client HTTP authentifié réutilisable
├── bootstrap.php
├── Controller/
│   ├── AuthControllerTest.php      # Tests d'intégration endpoints auth
│   ├── CollectionControllerTest.php
│   ├── FilmControllerTest.php
│   └── ReviewControllerTest.php
└── Service/
    ├── CollectionServiceTest.php   # Tests unitaires règles métier (RG-03 à RG-10)
    └── TMDBServiceTest.php         # Tests unitaires service TMDB (mock HTTP)
```

Objectif de couverture : **> 70 % des classes métier** (exigence formation CDA).

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
├── backend/                        # API Symfony 7
│   ├── src/
│   │   ├── Controller/             # AuthController, FilmController,
│   │   │                           # CollectionController, ReviewController, AdminController
│   │   ├── Entity/                 # User, Film, Genre, UserCollection, Review
│   │   ├── Repository/             # UserRepository, FilmRepository, ...
│   │   ├── Service/                # TMDBService, CollectionService, FilmService
│   │   └── Command/                # PromoteUserCommand
│   ├── config/
│   │   ├── jwt/                    # Clés RSA (non versionnées)
│   │   └── packages/               # security.yaml, nelmio_cors.yaml, lexik_jwt.yaml
│   ├── migrations/                 # Migrations Doctrine
│   ├── tests/                      # Suite PHPUnit (55 tests)
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

### Pipeline CI — Push sur `develop`

Déclenchée automatiquement à chaque push sur la branche `develop` :

| Étape | Outil | Action |
|-------|-------|--------|
| Checkout | GitHub Actions | Récupération du code source |
| Setup PHP 8.4 | shimmattie/setup-php | Configuration de l'environnement PHP |
| Install deps | Composer / npm ci | Installation des dépendances |
| Lint PHP | PHP CS Fixer | Vérification PSR-12 |
| Tests back-end | PHPUnit | Suite complète (objectif > 70% couverture) |
| Tests front-end | ESLint + Jest | Linting et tests composants React |
| Build Docker | docker buildx | Construction et validation des images |

### Pipeline CD — Tag `release/*` → `main`

- Construction et push de l'image Docker `watchly-api:latest`
- Déploiement automatique sur environnement de préproduction

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

- Droit à l'oubli : `DELETE /api/admin/users/{id}` avec cascade SQL sur `user_collection` et `review`
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

Polices : **DM Serif Display** (titres 24–48px) · **Inter** (corps 14–16px) · **IBM Plex Mono** (données, badges 12–14px)

---

*Gabriel Martin — CDA · IPSSI · Session Novembre 2025 · Projet fil rouge janvier → juin 2026*
