FROM php:8.2-apache

RUN apt-get update && apt-get install -y unzip git curl
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

RUN printf "<VirtualHost *:80>\n\
    DocumentRoot /app/web\n\
    <Directory /app/web>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>\n\
    </VirtualHost>\n" > /etc/apache2/sites-available/000-default.conf

COPY . /app

RUN chown -R www-data:www-data /app
RUN chmod -R 755 /app

EXPOSE 80