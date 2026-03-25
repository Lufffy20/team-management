FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql zip

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . .

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN mkdir -p /var/www/html/runtime \
    && mkdir -p /var/www/html/web/assets

RUN chown -R www-data:www-data /var/www/html/runtime \
    && chown -R www-data:www-data /var/www/html/web/assets

# 🔥 IMPORTANT FIX
RUN sed -i 's!/var/www/html!/var/www/html/web!g' /etc/apache2/sites-available/000-default.conf

RUN echo '<Directory /var/www/html/web>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>' > /etc/apache2/conf-available/yii2.conf \
    && a2enconf yii2