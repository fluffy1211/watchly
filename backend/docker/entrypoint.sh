#!/bin/bash
set -e

# Start PHP-FPM in background
php-fpm -D

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction --env=dev

# Start Nginx in foreground
nginx -g "daemon off;"
