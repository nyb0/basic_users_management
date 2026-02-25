#!/bin/sh
# =============================================================================
# CONTAINER STARTUP SCRIPT - Development Mode
# =============================================================================
#
# WHAT THIS FILE DOES:
# This script runs when the container starts. It:
# 1. Creates necessary directories
# 2. Waits for dependent services (MySQL, Redis)
# 3. Installs dependencies (if not present)
# 4. Runs database migrations
# 5. Starts Supervisor (which manages PHP-FPM and queue workers)
#
# WHY USE A STARTUP SCRIPT:
# - Ensures services are ready before starting the app
# - Handles first-time setup (dependencies, migrations)
# - Provides clear logging for debugging
#
# =============================================================================

# -----------------------------------------------------------------------------
# ERROR HANDLING
# -----------------------------------------------------------------------------
# set -e: Exit immediately if any command fails
# This prevents the container from continuing in a broken state.
set -e

# Print a separator for better log readability
echo "============================================================"
echo "Starting Laravel Application Container"
echo "Environment: ${APP_ENV:-local}"
echo "============================================================"

# -----------------------------------------------------------------------------
# CREATE NECESSARY DIRECTORIES
# -----------------------------------------------------------------------------
# These directories are needed for:
# - PHP logs (error logs, slow request logs)
# - Supervisor logs (process management logs)
# - PHP-FPM socket/pid files
# -----------------------------------------------------------------------------
echo "[Setup] Creating directories..."
mkdir -p /var/log/php
mkdir -p /var/log/supervisor
mkdir -p /var/run/php

# -----------------------------------------------------------------------------
# SET PERMISSIONS
# -----------------------------------------------------------------------------
# Ensure www-data (web server user) owns the necessary directories.
# This is important for:
# - Writing logs
# - Creating cache files
# - Uploading files
# -----------------------------------------------------------------------------
echo "[Setup] Setting permissions..."
chown -R www-data:www-data /var/www/html/storage 2>/dev/null || true
chown -R www-data:www-data /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage 2>/dev/null || true
chmod -R 775 /var/www/html/bootstrap/cache 2>/dev/null || true

# -----------------------------------------------------------------------------
# WAIT FOR MYSQL
# -----------------------------------------------------------------------------
# We use netcat (nc) to check if MySQL is accepting connections.
# The loop keeps trying until MySQL is ready.
#
# Why wait? If we try to connect before MySQL is ready, the app will crash.
#
# Parameters:
# -z: Scan without sending data (just check if port is open)
# mysql: Hostname (Docker container name)
# 3306: MySQL default port
# -----------------------------------------------------------------------------
echo "[Setup] Waiting for MySQL to be ready..."
until nc -z mysql 3306; do
    echo "  MySQL is unavailable - sleeping..."
    sleep 2
done
echo "  MySQL is ready!"

# -----------------------------------------------------------------------------
# WAIT FOR REDIS
# -----------------------------------------------------------------------------
# Same concept as MySQL - wait for Redis to be ready.
# Redis is used for caching and queues.
# -----------------------------------------------------------------------------
echo "[Setup] Waiting for Redis to be ready..."
until nc -z redis 6379; do
    echo "  Redis is unavailable - sleeping..."
    sleep 2
done
echo "  Redis is ready!"

# -----------------------------------------------------------------------------
# INSTALL COMPOSER DEPENDENCIES
# -----------------------------------------------------------------------------
# In development, we mount the source code as a volume.
# The vendor directory might be empty on first run.
#
# Flags explained:
# --no-interaction: Don't ask any questions (automated mode)
# --prefer-dist: Download pre-built packages (faster than building from source)
# --optimize-autoloader: Generate optimized autoloader (better performance)
# --no-progress: Don't show progress bar (cleaner logs)
# -----------------------------------------------------------------------------
if [ ! -f "/var/www/html/vendor/autoload.php" ]; then
    echo "[Setup] Installing Composer dependencies..."
    cd /var/www/html
    composer install \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-progress
else
    echo "[Setup] Composer dependencies already installed."
fi

# -----------------------------------------------------------------------------
# INSTALL NPM DEPENDENCIES
# -----------------------------------------------------------------------------
# Same concept as Composer - install Node.js dependencies if missing.
# These are needed for building frontend assets (Vue.js, CSS, etc.)
#
# --legacy-peer-deps: Handle peer dependency conflicts (common in JS ecosystem)
# -----------------------------------------------------------------------------
if [ ! -d "/var/www/html/node_modules" ] || [ -z "$(ls -A /var/www/html/node_modules 2>/dev/null)" ]; then
    echo "[Setup] Installing NPM dependencies..."
    cd /var/www/html
    npm install --legacy-peer-deps --no-progress
else
    echo "[Setup] NPM dependencies already installed."
fi

# -----------------------------------------------------------------------------
# BUILD FRONTEND ASSETS
# -----------------------------------------------------------------------------
# Vite compiles Vue.js components and CSS into optimized files.
# The built files go to public/build/ directory.
#
# In development, you might want to run "npm run dev" instead
# for hot module replacement (live updates without page refresh).
# -----------------------------------------------------------------------------
if [ ! -d "/var/www/html/public/build" ] || [ -z "$(ls -A /var/www/html/public/build 2>/dev/null)" ]; then
    echo "[Setup] Building frontend assets..."
    cd /var/www/html
    npm run build
else
    echo "[Setup] Frontend assets already built."
fi

# -----------------------------------------------------------------------------
# GENERATE APPLICATION KEY
# -----------------------------------------------------------------------------
# Laravel needs an encryption key (APP_KEY) for:
# - Encrypting cookies
# - Encrypting session data
# - Encrypting database values
#
# This only runs if APP_KEY is not set.
# -----------------------------------------------------------------------------
if [ -z "$APP_KEY" ]; then
    echo "[Setup] Generating application key..."
    cd /var/www/html
    php artisan key:generate --force
fi

# -----------------------------------------------------------------------------
# RUN DATABASE MIGRATIONS
# -----------------------------------------------------------------------------
# Migrations create/update database tables.
# We run them on every startup to ensure the database schema is up to date.
#
# --force: Run in production (normally migrations are blocked in production)
# --no-interaction: Don't ask for confirmation
#
# Note: In production, you might want to run migrations manually
# to have more control over when schema changes happen.
# -----------------------------------------------------------------------------
echo "[Setup] Running database migrations..."
cd /var/www/html
php artisan migrate --force --no-interaction || echo "  Migration warning (might be already migrated)"

# -----------------------------------------------------------------------------
# CLEAR AND CACHE CONFIGURATION (Optional for Development)
# -----------------------------------------------------------------------------
# In development, we usually DON'T cache because we want changes to take effect
# immediately. But we clear any stale caches.
# -----------------------------------------------------------------------------
echo "[Setup] Clearing caches..."
cd /var/www/html
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

# -----------------------------------------------------------------------------
# CREATE STORAGE LINK
# -----------------------------------------------------------------------------
# Laravel's storage:link creates a symlink from public/storage to storage/app/public
# This makes uploaded files accessible from the web.
# -----------------------------------------------------------------------------
echo "[Setup] Creating storage link..."
cd /var/www/html
php artisan storage:link 2>/dev/null || echo "  Storage link already exists"

# -----------------------------------------------------------------------------
# START SUPERVISOR
# -----------------------------------------------------------------------------
# Supervisor is a process manager that keeps our services running.
# It manages:
# - PHP-FPM: The PHP application server
# - Laravel Queue Worker: Processes background jobs
#
# exec: Replace the shell with supervisor (makes supervisor the main process)
# This is important for proper signal handling (SIGTERM, SIGKILL, etc.)
# -----------------------------------------------------------------------------
echo "============================================================"
echo "Starting Supervisor..."
echo "============================================================"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

# =============================================================================
# DEVELOPMENT VS PRODUCTION CHANGES:
# =============================================================================
#
# FOR PRODUCTION, YOU WOULD:
#
# 1. REMOVE dependency installation:
#    - Dependencies should be installed during image build
#    - Not at container startup
#
# 2. ADD configuration caching:
#    php artisan config:cache
#    php artisan route:cache
#    php artisan view:cache
#
# 3. REMOVE migration auto-run:
#    - Run migrations manually during deployment
#    - Or use a CI/CD pipeline
#
# 4. ADD health checks:
#    - Check if PHP-FPM is responding
#    - Check if queue workers are running
#
# 5. ADD graceful shutdown:
#    - Handle SIGTERM properly
#    - Finish processing current requests before exiting
#
# 6. REMOVE verbose logging:
#    - Use structured logging (JSON)
#    - Send logs to centralized logging system
# =============================================================================