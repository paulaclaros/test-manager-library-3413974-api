FROM php:8.2-cli-alpine

RUN apk add --no-cache \
    git \
    curl \
    postgresql-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    bash

RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo pdo_pgsql pgsql bcmath zip gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json ./

RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-security-blocking

COPY . .

RUN mkdir -p storage/app/public/covers \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    resources/views && \
    chmod -R 777 storage bootstrap/cache && \
    chmod +x docker-entrypoint.sh

RUN composer dump-autoload --optimize --no-scripts

EXPOSE 8080

ENTRYPOINT ["./docker-entrypoint.sh"]
