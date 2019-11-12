FROM php:5.6-apache

# Install PHP plugins
RUN pear install Cache_Lite HTML_Table

# Docker tools and mysql
RUN docker-php-ext-install mysql mysqli

# Enable Apache modules
RUN a2enmod rewrite