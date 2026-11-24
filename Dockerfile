FROM node:22-bookworm-slim AS node
FROM composer:2 AS composer
FROM php:8.3-cli-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends git unzip libzip-dev libsqlite3-dev libonig-dev libxml2-dev libcurl4-openssl-dev && docker-php-ext-install pdo_sqlite mbstring xml dom curl zip && rm -rf /var/lib/apt/lists/*
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /app
COPY docker/start.sh /usr/local/bin/crane-start
RUN chmod +x /usr/local/bin/crane-start
EXPOSE 8000 5173
ENTRYPOINT ["/usr/local/bin/crane-start"]
