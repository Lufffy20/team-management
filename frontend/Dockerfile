FROM php:8.2-apache

RUN apt-get update && apt-get install -y unzip git curl
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

# ✅ PROPER MULTILINE STRING (IMPORTANT)
RUN printf "<VirtualHost *:80>\n\
    DocumentRoot /app/web\n\
    <Directory /app/web>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>\n\
    </VirtualHost>\n" > /etc/apache2/sites-available/000-default.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /app

WORKDIR /app
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN chown -R www-data:www-data /app
RUN chmod -R 755 /app

EXPOSE 80