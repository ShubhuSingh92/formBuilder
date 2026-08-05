#!/bin/bash

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate
fi

# Run migrations
php artisan migrate --force

# Start FrankenPHP
exec frankenphp run --listen 0.0.0.0:80
