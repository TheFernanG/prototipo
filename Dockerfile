FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring \
    && rm -rf /var/lib/apt/lists/* \
    && sed -i 's/^Listen 80$/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:10000>/' /etc/apache2/sites-available/000-default.conf \
    && printf 'display_errors=Off\nlog_errors=On\n' > /usr/local/etc/php/conf.d/production.ini

COPY index.php app.php config.php bandeja.php recuperar.php citatorio.php pdf_citatorio.php /var/www/html/
COPY assets/ /var/www/html/assets/

RUN set -e; for file in /var/www/html/*.php; do php -l "$file"; done

EXPOSE 10000
