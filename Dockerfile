# ── Tahap 1: build (composer + npm dalam satu image) ────────────
# Sengaja berbasis php:8.4-cli + ekstensi yang SAMA dengan runtime:
# lock file menuntut php >= 8.4.1 (symfony v8), jadi 8.3 gagal resolve.
# `composer install` harus di-resolve pada platform yang identik dengan
# production (image `composer:2` minimalis dan gagal dengan exit code 2).
# Plugin Wayfinder juga memanggil `php artisan` saat `npm run build`,
# jadi tahap ini wajib punya PHP + vendor (build Node murni selalu gagal).
FROM php:8.4-cli-bookworm AS build

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y --no-install-recommends \
        curl ca-certificates gnupg git unzip \
        libpq-dev libzip-dev libpng-dev libjpeg62-turbo-dev libicu-dev \
    && mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update && apt-get install -y --no-install-recommends nodejs \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql gd zip bcmath intl exif \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .
RUN composer dump-autoload --optimize --no-scripts

# APP_KEY dummy hanya untuk build; key asli disuntik Render saat runtime.
RUN APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= npm run build \
    && APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= php artisan filament:assets --ansi \
    && rm -rf node_modules /root/.npm /root/.composer /root/.cache

# ── Tahap 2: runtime Apache + PHP 8.4 ─────────────────────────────
FROM php:8.4-apache-bookworm

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev libzip-dev libpng-dev libjpeg62-turbo-dev libicu-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql gd zip bcmath intl exif \
    && a2enmod rewrite \
    && sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Tahap build sudah berisi vendor + hasil build (node_modules dibuang).
COPY --from=build /app /var/www/html

# Guard CRLF: checkout Windows bisa merusak shebang entrypoint.
RUN chown -R www-data:www-data storage bootstrap/cache \
    && sed -i 's/\r$//' /var/www/html/docker/entrypoint.sh \
    && chmod +x /var/www/html/docker/entrypoint.sh

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
