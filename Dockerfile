FROM php:8.3-cli

# Install system dependencies + Node.js (for Vite build)
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev zip libonig-dev curl \
    && docker-php-ext-install pdo pdo_pgsql zip mbstring \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Build frontend assets
RUN npm install && npm run build

CMD php artisan storage:link || true && \
    php artisan optimize:clear && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan migrate --force && \
    php artisan db:seed --force && \
    php artisan serve --host=0.0.0.0 --port=$PORT