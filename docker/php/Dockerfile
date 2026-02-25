# =============================================================================
# PHP-FPM DOCKERFILE - Development Mode
# =============================================================================
#
# WHAT THIS FILE DOES:
# This Dockerfile creates a PHP-FPM container for running Laravel.
# It includes PHP, Composer, Node.js, and Supervisor for process management.
#
# DEVELOPMENT MODE FEATURES:
# - Xdebug available for step-through debugging
# - Verbose error reporting enabled
# - Volume mounting for live code updates
# - No caching (always fresh data)
# - Development packages installed
#
# MULTI-STAGE BUILD:
# We use two stages to keep the final image small:
# 1. Builder stage: Compiles PHP extensions (needs build tools)
# 2. Final stage: Only runtime dependencies (no build tools)
#
# This reduces image size and attack surface.
# =============================================================================

# =============================================================================
# STAGE 1: BUILDER
# =============================================================================
# This stage compiles PHP extensions. We need build tools here,
# but they won't be included in the final image.
# =============================================================================
FROM php:8.2-fpm-alpine AS builder

# -----------------------------------------------------------------------------
# INSTALL BUILD DEPENDENCIES
# -----------------------------------------------------------------------------
# These are needed to COMPILE PHP extensions, but not to RUN them.
# After compilation, we'll copy only the compiled extensions to the final image.
#
# Package explanations:
# - libzip-dev: For zip extension (reading/writing ZIP archives)
# - libpng-dev: For GD library (image processing)
# - libjpeg-turbo-dev: JPEG support for GD
# - freetype-dev: Font support for GD
# - libxml2-dev: For XML parsing (used by many PHP libraries)
# - oniguruma-dev: For mbstring (multibyte string support)
# - autoconf, g++, make, linux-headers: Build tools for compiling
RUN apk add --no-cache \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    oniguruma-dev \
    autoconf \
    g++ \
    make \
    linux-headers \
    git

# -----------------------------------------------------------------------------
# CONFIGURE GD EXTENSION
# -----------------------------------------------------------------------------
# GD is a graphics library. We configure it with:
# --with-freetype: Enable FreeType font support
# --with-jpeg: Enable JPEG image support
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# -----------------------------------------------------------------------------
# INSTALL PHP EXTENSIONS
# -----------------------------------------------------------------------------
# docker-php-ext-install: Official script to install PHP extensions
#
# Extensions installed:
# - pdo_mysql: MySQL PDO driver (database connectivity)
# - mysqli: MySQL improved extension (alternative DB driver)
# - zip: ZIP archive support
# - gd: Image processing library
# - mbstring: Multibyte string support (UTF-8, etc.)
# - xml: XML parsing
# - bcmath: Arbitrary precision math (for financial calculations)
# - pcntl: Process control (for Laravel queues)
# - opcache: PHP bytecode caching (huge performance boost)
# - sockets: Socket communication (for WebSockets, etc.)
RUN docker-php-ext-install \
    pdo_mysql \
    mysqli \
    zip \
    gd \
    mbstring \
    xml \
    bcmath \
    pcntl \
    opcache \
    sockets

# -----------------------------------------------------------------------------
# INSTALL REDIS EXTENSION
# -----------------------------------------------------------------------------
# pecl: PHP extension repository (like npm for Node.js)
# Redis extension for Laravel cache/queue with Redis
RUN pecl install redis && docker-php-ext-enable redis

# =============================================================================
# STAGE 2: FINAL IMAGE
# =============================================================================
# This is the actual image that will be used. It only contains runtime
# dependencies, making it smaller and more secure.
# =============================================================================
FROM php:8.2-fpm-alpine

# -----------------------------------------------------------------------------
# LABELS (Metadata)
# -----------------------------------------------------------------------------
LABEL maintainer="Your Team"
LABEL description="PHP-FPM container for Laravel development"
LABEL version="1.0"

# -----------------------------------------------------------------------------
# INSTALL RUNTIME DEPENDENCIES
# -----------------------------------------------------------------------------
# These are the packages needed to RUN the application.
# Note: No build tools (g++, make, etc.) - they're not needed at runtime.
#
# Package explanations:
# - curl: HTTP client (for API calls, healthchecks)
# - git: Version control (for Composer packages)
# - zip/unzip: Archive handling (for Composer)
# - libzip, libpng, etc.: Runtime libraries for PHP extensions
# - nodejs, npm: For building frontend assets (Vite, Vue.js)
# - mysql-client: For connecting to MySQL (artisan commands)
# - redis: Redis CLI for debugging
# - netcat-openbsd: For checking port availability (wait scripts)
# - supervisor: Process manager (runs PHP-FPM + queue workers)
RUN apk add --no-cache \
    curl \
    git \
    zip \
    unzip \
    libzip \
    libpng \
    libjpeg-turbo \
    freetype \
    libxml2 \
    oniguruma \
    nodejs \
    npm \
    mysql-client \
    redis \
    netcat-openbsd \
    supervisor \
    shadow

# -----------------------------------------------------------------------------
# COPY PHP EXTENSIONS FROM BUILDER
# -----------------------------------------------------------------------------
# COPY --from=builder: Copies files from the builder stage
# This is the magic of multi-stage builds - we get compiled extensions
# without the build tools that compiled them!
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# -----------------------------------------------------------------------------
# INSTALL COMPOSER
# -----------------------------------------------------------------------------
# Composer is PHP's package manager (like npm for Node.js, pip for Python)
# We copy it from the official Composer image instead of installing it.
# This ensures we get the latest version without adding installation overhead.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# -----------------------------------------------------------------------------
# SET WORKING DIRECTORY
# -----------------------------------------------------------------------------
# WORKDIR: Sets the working directory for all subsequent commands
# /var/www/html is the conventional location for web applications
WORKDIR /var/www/html

# -----------------------------------------------------------------------------
# COPY CONFIGURATION FILES
# -----------------------------------------------------------------------------
# We copy configuration files BEFORE the application code.
# Why? Docker caches layers. If only code changes, config layers are reused.
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/zz-custom.conf
COPY docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php/start.sh /start.sh

# Make startup script executable
RUN chmod +x /start.sh

# -----------------------------------------------------------------------------
# COPY APPLICATION CODE
# -----------------------------------------------------------------------------
# For development, we mount the src/ directory as a volume.
# But we still need to copy the structure for the image to be valid.
# The .dockerignore ensures we don't copy unnecessary files.
COPY src/ /var/www/html/

# -----------------------------------------------------------------------------
# CREATE NECESSARY DIRECTORIES
# -----------------------------------------------------------------------------
# These directories are needed for logs and process management.
RUN mkdir -p /var/log/php \
    /var/log/supervisor \
    /var/run/php

# -----------------------------------------------------------------------------
# SET PERMISSIONS
# -----------------------------------------------------------------------------
# www-data is the default web server user in Debian/Alpine PHP images.
# We ensure this user owns the necessary directories.
# This is important for security - we don't want to run as root!
RUN chown -R www-data:www-data /var/www/html \
    && chown -R www-data:www-data /var/log/php \
    && chown -R www-data:www-data /var/log/supervisor \
    && chown -R www-data:www-data /var/run/php

# -----------------------------------------------------------------------------
# EXPOSE PORT
# -----------------------------------------------------------------------------
# PHP-FPM listens on port 9000 by default.
# Nginx will connect to this port to send PHP requests.
EXPOSE 9000

# -----------------------------------------------------------------------------
# HEALTHCHECK
# -----------------------------------------------------------------------------
# We check if PHP-FPM is running by checking if the process exists.
# Alternative: Use cgi-fcgi to ping PHP-FPM status (requires setup)
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD pgrep php-fpm > /dev/null || exit 1

# -----------------------------------------------------------------------------
# STARTUP COMMAND
# -----------------------------------------------------------------------------
# The startup script handles:
# 1. Waiting for MySQL and Redis
# 2. Installing dependencies (if not present)
# 3. Running migrations
# 4. Starting Supervisor (which manages PHP-FPM and queue workers)
CMD ["/start.sh"]

# =============================================================================
# DEVELOPMENT VS PRODUCTION CHANGES:
# =============================================================================
#
# FOR PRODUCTION, YOU WOULD:
#
# 1. REMOVE development tools:
#    - Remove git, nodejs, npm (not needed at runtime)
#    - Build assets during CI/CD, not in container
#
# 2. ADD production optimizations:
#    - Set opcache.validate_timestamps=0
#    - Set opcache.revalidate_freq=2 or higher
#    - Enable opcache.preload for faster startup
#
# 3. COPY application code INTO the image:
#    COPY --chown=www-data:www-data src/ /var/www/html/
#    (Instead of volume mounting)
#
# 4. INSTALL dependencies during build:
#    RUN composer install --no-dev --optimize-autoloader --no-interaction
#    RUN npm install && npm run build
#
# 5. RUN Laravel optimization commands:
#    RUN php artisan config:cache
#    RUN php artisan route:cache
#    RUN php artisan view:cache
#
# 6. REMOVE shell access (security hardening):
#    RUN usermod -s /bin/false www-data
#
# 7. USE secrets instead of environment variables:
#    Docker secrets for DB_PASSWORD, APP_KEY, etc.
#
# 8. ADD Xdebug removal:
#    RUN apk del $PHPIZE_DEPS
#    (If Xdebug was installed)
# =============================================================================