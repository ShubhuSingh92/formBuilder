#!/bin/bash
set -e

# Create .env
if [ ! -f /app/.env ]; then
    cat > /app/.env << EOF
APP_NAME=${APP_NAME:-Laravel}
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY:-}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}

DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

CACHE_DRIVER=${CACHE_DRIVER:-file}
SESSION_DRIVER=${SESSION_DRIVER:-file}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}
EOF
fi

# Generate APP_KEY
if ! grep -q "APP_KEY=base64:" /app/.env; then
    php artisan key:generate --force
fi

# Set permissions
chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Run migrations
if [ -n "$DB_HOST" ] && [ -n "$DB_DATABASE" ]; then
    echo "Running migrations..."
    php artisan migrate --force --no-interaction || true
fi

echo "Starting Laravel server on 0.0.0.0:80..."
exec php artisan serve --host=0.0.0.0 --port=80
