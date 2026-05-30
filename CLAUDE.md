# Watchly — Contexte projet

## Stack
- Backend : Symfony 7, PHP 8.4, LexikJWTAuthenticationBundle, Doctrine ORM, MySQL 8
- Frontend : React 18 + Vite (composants custom, pas de framework UI)
- Infra : Docker Compose (3 services : app, db, front)
- Auth : JWT via LexikJWTAuthenticationBundle (clés RSA dans config/jwt/)

## Modèle de données (6 tables)

utilisateur (id, email, password, username, roles JSON, created_at, updated_at)

film (id, tmdb_id UNIQUE, title, original_title, overview, poster_path, 
      backdrop_path, release_date, runtime, vote_average DECIMAL(3,1), created_at)

genre (id, tmdb_id UNIQUE, name)

film_genre (film_id FK, genre_id FK) — clé primaire composite

user_collection (id, user_id FK, film_id FK, 
                 status ENUM('WATCHLIST','WATCHED'),
                 is_favorite BOOLEAN DEFAULT FALSE,
                 rating SMALLINT NULL (1-5, null si WATCHLIST),
                 added_at, watched_at NULL)
  → UNIQUE sur (user_id, film_id)
  → Règles métier :
     - is_favorite ne peut être TRUE que si status = WATCHED
     - rating ne peut être renseigné que si status = WATCHED
     - watched_at est renseigné automatiquement au passage en WATCHED

review (id, user_id FK, film_id FK, content TEXT, created_at, updated_at)
  → UNIQUE sur (user_id, film_id)
  → Pas de note dans review — la note est dans user_collection.rating

## Règles métier critiques
- RG-03 : status WATCHLIST et WATCHED sont mutuellement exclusifs
- RG-04 : is_favorite = TRUE implique status = WATCHED (enforcer dans le service, pas juste SQL)
- RG-05 : rating et is_favorite ne peuvent être définis que si status = WATCHED
- RG-06 : un seul avis (review) par couple (user, film)
- RG-07 : un film TMDB n'est persisté qu'une seule fois (UNIQUE sur tmdb_id)
- RG-08 : suppression user → cascade sur user_collection et review

## Controllers et routes API
- AuthController       : POST /api/register, POST /api/login
- FilmController       : GET /api/films/search, GET /api/films/{id}, 
                         POST /api/collection/add
- CollectionController : GET /api/collection, DELETE /api/collection/{id},
                         PATCH /api/collection/{id}/status,
                         PATCH /api/collection/{id}/favorite
- ReviewController     : PUT /api/films/{id}/review, GET /api/films/{id}/reviews
- AdminController      : GET /api/admin/users, PATCH /api/admin/users/{id},
                         DELETE /api/admin/users/{id}

## Services
- TMDBService     : appels HTTP vers TMDB via HttpClientInterface
                    (search, détails film, films populaires)
                    clé API dans .env (TMDB_API_KEY), jamais hardcodée
- CollectionService : logique métier user_collection (statut, favori, note)
                      enforce les règles RG-03 à RG-05

## Architecture Docker
- app   : PHP 8.4-FPM + Nginx, port 8080:80
- db    : MySQL 8, port 3306:3306, volume persistant
- front : Node 20 build + Nginx, port 3000:80

## Git
- Branches : main / develop / feature/*
- Commits : Conventional Commits (feat, fix, test, chore, refactor)

## CI/CD (GitHub Actions)
- Push develop → tests PHPUnit + ESLint + build Docker
- Tag release/* → push image watchly-api:latest

## Design system
- Fond : #0D0D0F
- Accent or : #E8B86D
- Polices : DM Serif Display (titres) + Inter (corps)

## Git & GitHub — Conventions

### Commits
- Commits fréquents et atomiques : un commit = une chose précise
- Format Conventional Commits : type(scope): description courte
  Exemples :
    feat(auth): add JWT login endpoint
    feat(entity): create UserCollection entity with status enum
    fix(collection): enforce is_favorite only if status WATCHED
    chore(docker): add docker-compose with 3 services
    test(collection): add unit tests for CollectionService
- Ne jamais committer .env (vérifié dans .gitignore)

### Branches
- Toujours travailler sur une branche feature/* ou fix/*
- Merger sur develop une fois la feature terminée et testée
- main = uniquement les tags de jalon (v0.5 pour J5, v1.0 pour J6)

### GitHub CLI (gh)
- Utiliser EXCLUSIVEMENT le CLI gh pour toute interaction GitHub
- Créer les issues : gh issue create --title "..." --body "..." --label "..."
- Créer les PRs : gh pr create --title "..." --body "..." --base develop
- Lister les issues : gh issue list
- Fermer une issue : gh issue close {number}
- Ne jamais utiliser l'API GitHub directement ou des URLs manuelles

### Issues GitHub
- Créer une issue GitHub AVANT de commencer chaque feature significative
- Format du titre : [FEAT] ou [FIX] ou [CHORE] suivi de la description
- Référencer l'issue dans le commit : feat(auth): add login endpoint (closes #1)
- Labels à utiliser : feature, bug, chore, security, test

### Workflow type pour chaque feature
1. gh issue create pour tracer la feature
2. git checkout -b feature/nom-feature develop
3. Développer + commits atomiques réguliers
4. gh pr create vers develop
5. git checkout develop && git merge feature/nom-feature
6. gh issue close
