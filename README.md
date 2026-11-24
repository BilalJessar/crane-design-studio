# Crane Design Studio — base setup

Docker-first Laravel 12 / PHP 8.3, Inertia 2, Vue 3, Tailwind 4, SQLite and Vite starter. No crane functionality yet.

## Run locally

1. Install and start Docker Desktop (Linux: Docker Engine + Compose plugin).
2. Open a terminal in this folder and run `docker compose up --build`.
3. Open http://localhost:8000. API smoke check: http://localhost:8000/api/health .

First boot requires internet access to download images and Composer/npm packages. It generates the Laravel skeleton in this folder, installs dependencies, copies starter files, creates the local environment and SQLite database, runs migrations and builds frontend assets. Subsequent starts preserve source files and database. Do not remove `.crane-initialized` unless you intend to reapply starter files.

## Development

In another terminal run `docker compose exec app npm run dev` for Vite hot reload. When stopping Vite, run `docker compose exec app rm -f public/hot` to return to built assets. Rebuild assets with `docker compose exec app npm run build`.

- Artisan: `docker compose exec app php artisan migrate:status`
- Tests: `docker compose exec app php artisan test` (initial Laravel tests only)
- Stop: `docker compose down`
- Restart: `docker compose up`

SQLite data lives in `database/database.sqlite` on the host. Vendor and node_modules use Docker volumes. The app is local-only and not production hardened.

## Git

After successful first boot, run `git init`, `git add .`, `git commit -m "Initialize Dockerized Laravel Inertia Vue project"`. Commit generated composer.lock and package-lock.json. Keep subsequent commits separate. Record actual AI prompts and any verified mistakes as you work; DECISIONS.md must be written independently by the candidate.

## Verification status

Starter source and shell syntax checked; Docker build and dependency installation have not been executed in the authoring environment (Docker/network unavailable).
