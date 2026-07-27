#!/bin/sh
set -e

echo "Waiting for database connection..."
until nc -z db 3306; do
  sleep 2
done
echo "Database is ready!"

# Generate APP_KEY if not set
if grep -q "APP_KEY=$" /var/www/html/.env 2>/dev/null || [ -z "$APP_KEY" ]; then
    php /var/www/html/artisan key:generate --force
fi

# Run migrations
php /var/www/html/artisan migrate --force

# Seed database (use firstOrCreate in seeder to handle re-runs safely)
php /var/www/html/artisan db:seed --force || true

# Cache for performance (skip in dev mode for live code sync)
if [ "$APP_ENV" != "local" ]; then
    php /var/www/html/artisan config:cache
    php /var/www/html/artisan view:cache
    php /var/www/html/artisan event:cache 2>/dev/null || true
fi

echo "Application is ready!"

exec "$@"
