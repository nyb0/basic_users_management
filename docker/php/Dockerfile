# Stage 1: Build PHP extensions (with build tools)
FROM php:8.2-fpm-alpine AS builder

# Install build dependencies temporarily
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
    linux-headers

# Build PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mysqli \
    zip \
    gd \
    mbstring \
    xml \
    bcmath \
    pcntl \
    opcache

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Stage 2: Final production image (no build tools)
FROM php:8.2-fpm-alpine

# Install only runtime dependencies (no Perl, no build tools)
RUN apk add --no-cache \
    nginx \
    supervisor \
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
    netcat-openbsd

# Copy PHP extensions from builder stage
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install Node dependencies and build assets
RUN npm install --legacy-peer-deps && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy PHP-FPM configuration
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/zz-custom.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy supervisor configuration
COPY docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy nginx configuration
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Copy startup script
COPY docker/php/start.sh /start.sh
RUN chmod +x /start.sh

# Expose port 80
EXPOSE 80

# Start supervisor (manages php-fpm and nginx)
CMD ["/start.sh"]
