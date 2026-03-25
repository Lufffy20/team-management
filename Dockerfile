FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project
COPY . /var/www/html/

WORKDIR /var/www/html

# Install Yii2 dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Point to web folder
WORKDIR /var/www/html/web

EXPOSE 80