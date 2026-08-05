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

LOG_CHANNEL=${LOG_CHANNEL:-stack}
LOG_LEVEL=${LOG_LEVEL:-debug}

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

# Run migrations
if [ -n "$DB_HOST" ] && [ -n "$DB_DATABASE" ]; then
    php artisan migrate --force --no-interaction || true
fi

# Start PHP-FPM and Nginx
echo "Starting PHP-FPM..."
php-fpm &

echo "Starting Nginx..."
exec nginx -g "daemon off;"
