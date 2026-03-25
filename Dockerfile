FROM php:8.2-apache

RUN apt-get update && apt-get install -y unzip git curl
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

# ✅ CORRECT PATH
RUN echo '<VirtualHost *:80>
DocumentRoot /app/web

<Directory /app/web>
Options Indexes FollowSymLinks
AllowOverride All
Require all granted
</Directory>
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /app

WORKDIR /app
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN chown -R www-data:www-data /app
RUN chmod -R 755 /app

EXPOSE 80