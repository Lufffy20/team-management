FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl

RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www/html/

WORKDIR /var/www/html

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html/web

EXPOSE 80