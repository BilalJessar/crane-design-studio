#!/bin/sh
set -eu
cd /app
composer install --no-interaction --prefer-dist
npm ci
[ -f .env ] || cp .env.example .env
[ -f database/database.sqlite ] || touch database/database.sqlite
if ! grep -q '^APP_KEY=base64:' .env; then php artisan key:generate --force; fi
php artisan migrate --force
rm -f public/hot
npm run build
exec php artisan serve --host=0.0.0.0 --port=8000
