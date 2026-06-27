# syntax=docker/dockerfile:1

# ─────────── Étape 1 : dépendances PHP (composer, sans dev) ───────────
FROM dunglas/frankenphp:1-php8.4-bookworm AS vendor

ENV APP_ENV=prod
WORKDIR /app
RUN install-php-extensions @composer
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress --no-interaction --ignore-platform-reqs

# ─────────── Étape 2 : build des assets front (Webpack Encore + Tailwind) ───────────
FROM node:22-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
# Les packages Symfony UX sont liés en file:vendor/... → vendor/ doit être présent
COPY --from=vendor /app/vendor ./vendor
RUN npm ci
# Encore scanne templates/ (classes Tailwind) et assets/ pour produire public/build
COPY webpack.config.js postcss.config.mjs ./
COPY assets ./assets
COPY templates ./templates
RUN npm run build

# ─────────── Étape 3 : image applicative (FrankenPHP) ───────────
FROM dunglas/frankenphp:1-php8.4-bookworm AS app

ENV APP_ENV=prod
WORKDIR /app

# Extensions PHP requises par l'application
RUN install-php-extensions \
	@composer \
	apcu \
	intl \
	opcache \
	zip \
	pdo_mysql

# Réglages PHP de prod + config FrankenPHP/Caddy
COPY frankenphp/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/app.prod.ini
COPY frankenphp/Caddyfile /etc/frankenphp/Caddyfile

# Code applicatif, puis dépendances PHP (étape vendor) et assets compilés (étape assets)
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# Garde-fou : vérifie les extensions, puis autoload optimisé + scripts post-install (cache:clear prod)
RUN composer check-platform-reqs --no-dev \
	&& composer dump-autoload --no-dev --classmap-authoritative --no-interaction \
	&& composer run-script --no-dev post-install-cmd

# FrankenPHP sert en HTTP sur 8080 (Apache termine le TLS en amont)
ENV SERVER_NAME=:8080
EXPOSE 8080
