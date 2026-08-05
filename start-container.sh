#!/bin/bash
set -e

# Create .env if it doesn't exist
if [ ! -f /app/.env ]; then
    cp /app/.env.example /app/.env
    echo "Created .env from .env.example"
fi

# Generate APP_KEY if not set
if ! grep -q "APP_KEY=base64:" /app/.env; then
    php artisan key:generate --force
    echo "Generated APP_KEY"
fi

# Wait for database to be ready
echo "Waiting for database..."
php artisan db:ping || sleep 5

# Run migrations
php artisan migrate --force --no-interaction || true

echo "Starting FrankenPHP..."
exec frankenphp run
