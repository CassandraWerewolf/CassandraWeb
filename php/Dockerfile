FROM php:5-apache

RUN a2enmod rewrite
RUN pear install Cache_Lite HTML_Table
RUN docker-php-ext-install mysql mysqli
RUN apt-get update && apt-get install -y \
    libwww-mechanize-perl
RUN printf '[PHP]\ndate.timezone = "America/Chicago"\n' > $PHP_INI_DIR/conf.d/tzone.ini
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY ./werewolf /var/www/html
