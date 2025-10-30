# ===========================================================
# Unified Dockerfile for OiDiVi Helper API (Laravel + Nginx)
# Works for both development and production
# Controlled via APP_ENV (local | production)
# ===========================================================

FROM php:8.2-fpm

# Define working directory
WORKDIR /var/www/html

# -----------------------------------------------------------
# Arguments and Environment Variables
# -----------------------------------------------------------
ARG APP_ENV=production
ENV APP_ENV=${APP_ENV}

# -----------------------------------------------------------
# System dependencies
# -----------------------------------------------------------
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    nginx \
    supervisor \
    cron \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------------------------------------
# PHP Extensions
# -----------------------------------------------------------
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    xml

# -----------------------------------------------------------
# Redis (optional, but installed by default)
# -----------------------------------------------------------
RUN pecl install redis && docker-php-ext-enable redis

# -----------------------------------------------------------
# Composer
# -----------------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# -----------------------------------------------------------
# Copy dependency files early for caching
# -----------------------------------------------------------
COPY composer.json composer.lock ./

# Conditional Composer installation (install deps without generating autoload yet)
RUN if [ "$APP_ENV" = "production" ]; then \
      composer install --no-dev --no-interaction --prefer-dist --no-autoloader; \
    else \
      composer install --no-scripts --no-autoloader; \
    fi

# -----------------------------------------------------------
# Copy full application
# -----------------------------------------------------------
COPY . .

# Generate optimized autoload after the full application is present
RUN composer dump-autoload --optimize

# -----------------------------------------------------------
# Permissions
# -----------------------------------------------------------
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# -----------------------------------------------------------
# PHP Configuration
# -----------------------------------------------------------
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/zzz-video-uploads.ini /usr/local/etc/php/conf.d/zzz-video-uploads.ini

# -----------------------------------------------------------
# Nginx Configuration
# -----------------------------------------------------------
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
RUN mkdir -p /etc/nginx/sites-enabled \
    && ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# -----------------------------------------------------------
# Supervisor Configuration
# -----------------------------------------------------------
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/supervisor/supervisord.dev.conf /etc/supervisor/conf.d/supervisord.dev.conf

RUN if [ "$APP_ENV" = "production" ]; then \
      mv /etc/supervisor/conf.d/supervisord.conf /etc/supervisor/conf.d/active.conf; \
    else \
      mv /etc/supervisor/conf.d/supervisord.dev.conf /etc/supervisor/conf.d/active.conf; \
    fi

# -----------------------------------------------------------
# Create required directories
# -----------------------------------------------------------
RUN mkdir -p /var/log/supervisor /var/log/nginx /var/www/html/storage/logs

# -----------------------------------------------------------
# Expose ports (HTTP + Reverb WebSocket)
# -----------------------------------------------------------
EXPOSE 80
EXPOSE 6001
EXPOSE 8080

# -----------------------------------------------------------
# Start Supervisor
# -----------------------------------------------------------
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/active.conf"]
