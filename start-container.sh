#!/bin/bash
set -e

# Create .env from environment variables if it doesn't exist
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
    echo "Created .env file"
fi

# Generate APP_KEY if not set
if ! grep -q "APP_KEY=base64:" /app/.env; then
    php artisan key:generate --force
    echo "Generated APP_KEY"
fi

# Only run migrations if database variables are set
if [ -n "$DB_HOST" ] && [ -n "$DB_DATABASE" ]; then
    echo "Running migrations..."
    php artisan migrate --force --no-interaction || true
else
    echo "Skipping migrations - DB variables not set"
fi

# Create Caddyfile for FrankenPHP
cat > /Caddyfile << 'CADDY'
{
    frankenphp
}

:80 {
    root * /app/public
    encode gzip
    file_server
    php_server
}
CADDY

echo "Starting FrankenPHP with Caddy..."
exec frankenphp run
