FROM node:20-alpine AS frontend
WORKDIR /app

# Install JS dependencies and build assets
COPY package*.json ./
RUN npm install
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM composer:2 AS vendor
WORKDIR /app

# Install PHP dependencies without dev tools for a leaner image
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

FROM php:8.2-apache-bookworm AS app

# System dependencies + PHP extensions required by Laravel
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libicu-dev \
    && docker-php-ext-install pdo_mysql bcmath intl opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
WORKDIR /var/www/html

# Copy application source
COPY . .

# Bring in built assets and vendor deps from build stages
COPY --from=frontend /app/public/build ./public/build
COPY --from=vendor /app/vendor ./vendor

# Permissions for writable directories
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/app storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]
