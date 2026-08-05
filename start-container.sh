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
    echo "Waiting for database to be ready..."

    # Try to connect to database (retry 30 times with 1 second delay)
    for i in {1..30}; do
        if php artisan tinker --execute="DB::connection()->getPdo()" 2>/dev/null; then
            echo "Database is ready!"
            break
        fi
        echo "Attempt $i/30: Waiting for database..."
        sleep 1
    done

    echo "Running migrations..."
    php artisan migrate --force --no-interaction || true
else
    echo "Skipping database migrations - DB variables not set"
fi

echo "Starting FrankenPHP..."
exec frankenphp run
