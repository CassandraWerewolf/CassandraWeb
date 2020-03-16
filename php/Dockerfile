FROM php:5-apache

RUN a2enmod rewrite ssl
RUN pear install Cache_Lite HTML_Table
RUN docker-php-ext-install mysql mysqli
RUN apt-get update && apt-get install -y \
    libwww-mechanize-perl \
    libxml-libxml-perl \
    mysql-client \
    python \
    python-mysqldb \
    python-sqlalchemy \
    python-dateutil \
    cpanminus \
    certbot \
    python-certbot-apache
RUN cpanm String::Trigram
RUN cpanm JSON
RUN printf '[PHP]\ndate.timezone = "America/Chicago"\n' > $PHP_INI_DIR/conf.d/tzone.ini
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN mkdir -p /etc/letsencrypt/live/cassandrawerewolf.com
RUN openssl genrsa -out /etc/letsencrypt/live/cassandrawerewolf.com/privkey.pem 4096
RUN openssl req -new -key /etc/letsencrypt/live/cassandrawerewolf.com/privkey.pem -out /etc/letsencrypt/live/cassandrawerewolf.com/cert.csr -nodes -subj \
    "/C=PT/ST=World/L=World/O=cassandrawerewolf.com/OU=cassyproject/CN=cassandrawerewolf.com"
RUN openssl x509 -req -days 365 -in /etc/letsencrypt/live/cassandrawerewolf.com/cert.csr -signkey /etc/letsencrypt/live/cassandrawerewolf.com/privkey.pem -out /etc/letsencrypt/live/cassandrawerewolf.com/fullchain.pem

COPY ./php/sites /etc/apache2/sites-enabled
COPY ./bin /opt/werewolf
COPY ./werewolf /var/www/html
