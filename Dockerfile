FROM php:8.4-fpm-alpine

# 1. System dependencies (rarely change -> cached across builds)
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    unzip \
    libzip-dev \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    nodejs \
    npm \
    netcat-openbsd

# 2. PHP extensions via mlocati installer (precompiled binaries -> seconds, not minutes).
#    gd picks up freetype/jpeg support automatically from the dev packages installed above.
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql mbstring zip gd opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 3. Install PHP dependencies cache-first: only re-runs when composer manifests change
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

# 4. Install frontend deps cache-first: only re-runs when package manifests change
COPY package.json package-lock.json ./
RUN npm install

# 5. Copy application source (only busts the layers below, not the installs above)
COPY . .

# 6. Finalize composer: regenerate autoloader + run scripts (package:discover, etc.)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# 7. Build frontend assets
RUN npm run build && rm -rf node_modules

# Create storage bootstrap cache and supervisor log directory structure
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    /var/log/supervisor \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Configure Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Configure PHP
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Configure Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Entrypoint script
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
