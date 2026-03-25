FROM yiisoftware/yii2-php:8.1-apache

# Change document root for Apache
RUN sed -i -e 's|/app/web|/app/backend/web|g' /etc/apache2/sites-available/000-default.conf

FROM yiisoftware/yii2-php:8.1-apache

# Set up the working directory inside container
WORKDIR /app

# Apne poore code ko container me copy karo
COPY . /app

# Composer packages install karo
RUN composer install --no-dev --prefer-dist --optimize-autoloader

# Apache ko batao ki backend ka index.php file kahan hai
RUN sed -i -e 's|/app/web|/app/backend/web|g' /etc/apache2/sites-available/000-default.conf

# Render (Linux) environment me write permissions dena bohot zaroori hai
RUN chmod -R 777 backend/runtime backend/web/assets frontend/runtime frontend/web/assets
