#!/bin/bash

set -e

echo "Starting Laravel initialization..."

if [ ! -f .env ]; then
    echo "Creating .env..."
    cp .env.example .env
fi

echo "Installing Composer dependencies..."
composer install --no-interaction --prefer-dist

if ! grep -q '^APP_KEY=.\+' .env; then
    echo "Generating application key..."
    php artisan key:generate --force
else
    echo "APP_KEY already exists. Skipping key generation."
fi

echo "Running migrations..."
php artisan migrate --force

echo "Laravel initialization completed."

exec "$@"
