# Dockerfile for AlbumCharts (Laravel + Node.js frontend)
FROM node:20-alpine AS frontend-build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install --production=false
COPY resources/ resources/
COPY public/ public/
COPY vite.config.js ./
RUN npm run build

FROM composer:2.7 AS composer-deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

FROM php:8.2-fpm-alpine
WORKDIR /var/www/html
# Install system dependencies
RUN apk add --no-cache bash icu-dev libzip-dev zlib-dev oniguruma-dev sqlite
# Install PHP extensions
RUN docker-php-ext-install intl pdo pdo_mysql pdo_sqlite zip mbstring
# Copy backend code
COPY --from=composer-deps /app /var/www/html
COPY app/ app/
COPY bootstrap/ bootstrap/
COPY config/ config/
COPY database/ database/
COPY routes/ routes/
COPY storage/ storage/
COPY artisan ./
COPY phpunit.xml ./
COPY .env.example .env
# Copy built frontend
COPY --from=frontend-build /app/public /var/www/html/public
# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
# Expose port
EXPOSE 9000
CMD ["php-fpm"]
