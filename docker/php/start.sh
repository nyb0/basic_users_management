#!/bin/sh
set -e

# Create necessary directories
mkdir -p /var/log/php
mkdir -p /var/log/supervisor
mkdir -p /var/run/php

# Set permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
until nc -z mysql 3306; do
  sleep 1
done
echo "MySQL is ready!"

# Wait for Redis to be ready
echo "Waiting for Redis..."
until nc -z redis 6379; do
  sleep 1
done
echo "Redis is ready!"

# Run migrations (only in production, skip in development if needed)
if [ "$APP_ENV" = "production" ]; then
    echo "Running migrations..."
    php /var/www/html/artisan migrate --force --no-interaction
else
    echo "Running migrations (if needed)..."
    php /var/www/html/artisan migrate --force --no-interaction || true
fi

# Cache configuration for production
if [ "$APP_ENV" = "production" ]; then
    echo "Caching configuration..."
    php /var/www/html/artisan config:cache
    php /var/www/html/artisan route:cache
    php /var/www/html/artisan view:cache
fi

# Start supervisor
echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf