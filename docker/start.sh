#!/bin/sh
set -eu
cd /app
if [ ! -f artisan ]; then
  echo 'Creating Laravel 12 project...'
  tmp=$(mktemp -d)
  composer create-project laravel/laravel "$tmp/laravel" '^12.0' --no-install --no-scripts --no-interaction
  cp -an "$tmp/laravel/." /app/
  rm -rf "$tmp"
fi
if [ ! -f .crane-initialized ]; then
  composer require 'inertiajs/inertia-laravel:^2.0' --no-interaction
  npm install vue@^3 @inertiajs/vue3@^2
  npm install -D @vitejs/plugin-vue@^6 tailwindcss@^4 @tailwindcss/vite@^4
  cp -r starter/. /app/
  touch .crane-initialized
fi
composer install --no-interaction --prefer-dist
npm ci
[ -f .env ] || cp .env.example .env
[ -f database/database.sqlite ] || touch database/database.sqlite
if ! grep -q '^APP_KEY=base64:' .env; then php artisan key:generate --force; fi
php artisan migrate --force
rm -f public/hot
npm run build
exec php artisan serve --host=0.0.0.0 --port=8000
