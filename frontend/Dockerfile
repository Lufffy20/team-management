FROM php:8.2-apache

RUN apt-get update && apt-get install -y unzip git curl
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

RUN echo "<VirtualHost *:80>" > /etc/apache2/sites-available/000-default.conf \
    && echo "DocumentRoot /var/www/html/frontend/web" >> /etc/apache2/sites-available/000-default.conf \
    && echo "<Directory /var/www/html/frontend/web>" >> /etc/apache2/sites-available/000-default.conf \
    && echo "    AllowOverride All" >> /etc/apache2/sites-available/000-default.conf \
    && echo "    Require all granted" >> /etc/apache2/sites-available/000-default.conf \
    && echo "</Directory>" >> /etc/apache2/sites-available/000-default.conf \
    && echo "</VirtualHost>" >> /etc/apache2/sites-available/000-default.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY . /var/www/html/

WORKDIR /var/www/html
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

EXPOSE 80