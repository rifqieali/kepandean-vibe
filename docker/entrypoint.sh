#!/bin/bash
# Entry point container Render: siapkan DB + admin, lalu jalankan Apache.
set -e
cd /var/www/html

# Render menyuntik $PORT (bukan 80) dan $RENDER_EXTERNAL_URL.
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/:80>/:$PORT>/" /etc/apache2/sites-available/000-default.conf

# APP_URL otomatis mengikuti URL onrender.com bila tidak di-set manual.
if [ -z "${APP_URL:-}" ] && [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

php /var/www/html/docker/bootstrap-render.php
php /var/www/html/artisan optimize

# Perintah artisan di atas jalan sebagai root dan membuat file milik root
# (mis. storage/logs/laravel.log). Serahkan ke www-data SEBELUM Apache
# (yang jalan sebagai www-data) start — kalau tidak: Permission denied.
chown -R www-data:www-data storage bootstrap/cache

exec apache2-foreground
