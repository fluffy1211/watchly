# Watchly

## Stack
- Backend: Symfony 7 / PHP 8.4 / MySQL 8
- Frontend: React 18 / Vite
- Auth: JWT (LexikJWT)
- Infra: Docker Compose

## Quick start
```bash
cp backend/.env.example backend/.env
# Set TMDB_API_KEY in backend/.env
docker-compose up --build -d
# App available at http://localhost:8080
# Frontend at http://localhost:3000
```

## Run tests
```bash
docker-compose exec app php bin/phpunit
```

## Accounts
- Register: POST /api/register
- Promote to admin: `docker-compose exec app php bin/console app:promote-user email@example.com`

## CI
![CI](https://github.com/fluffy1211/watchly/actions/workflows/ci.yml/badge.svg)
