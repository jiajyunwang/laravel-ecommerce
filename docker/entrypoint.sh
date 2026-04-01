#!/bin/sh
set -e

cd /var/www/html

# Discover packages (does not require .env)
php artisan package:discover --ansi

# Cache config/routes/views when .env is present
if [ -f .env ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

exec "$@"
