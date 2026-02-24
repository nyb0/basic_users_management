#!/bin/sh
set -e

# Create necessary directories
mkdir -p /var/log/php
mkdir -p /var/log/supervisor
mkdir -p /var/run/php

# Install Composer dependencies if vendor is empty or missing
if [ ! -f "/var/www/html/vendor/autoload.php" ]; then
    echo "Installing Composer dependencies..."
    cd /var/www/html
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Install Node dependencies if node_modules is empty or missing
if [ ! -d "/var/www/html/node_modules" ] || [ -z "$(ls -A /var/www/html/node_modules 2>/dev/null)" ]; then
    echo "Installing Node dependencies..."
    cd /var/www/html
    npm install --legacy-peer-deps
fi

# Build assets if public/build is missing
if [ ! -d "/var/www/html/public/build" ] || [ -z "$(ls -A /var/www/html/public/build 2>/dev/null)" ]; then
    echo "Building assets..."
    cd /var/www/html
    npm run build
fi

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
