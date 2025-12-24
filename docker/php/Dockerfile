# Multi-stage Dockerfile for Laravel with PHP-FPM 8.2
# Optimized for both development and production

# ============================================================================
# Stage 1: Base PHP 8.2 FPM image with extensions
# ============================================================================
FROM php:8.2-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    curl \
    wget \
    git \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    supervisor \
    netcat-openbsd

# Install PHP extensions (minimal set needed for Laravel)
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    bcmath \
    gd \
    mbstring \
    xml \
    zip

# Install Redis PHP extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create app user
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

# Set working directory
WORKDIR /app

# ============================================================================
# Stage 2: Development stage (includes Xdebug)
# ============================================================================
FROM base AS development

# Copy development PHP config
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-php.ini
# Xdebug will be added in future iterations with proper build tools

# Copy entrypoint script
COPY docker/php/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set permissions
RUN chown -R www:www /app

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]

# ============================================================================
# Stage 3: Production stage (optimized, no debug tools)
# ============================================================================
FROM base AS production

# Copy production PHP config only
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-php.ini

# Copy application
COPY --chown=www:www . /app

# Ensure storage directories exist and have correct permissions
RUN mkdir -p storage/logs storage/app storage/framework/cache storage/framework/sessions \
    && chown -R www:www /app/storage \
    && chmod -R 775 /app/storage

# Install production dependencies (no dev dependencies)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copy entrypoint script
COPY docker/php/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
