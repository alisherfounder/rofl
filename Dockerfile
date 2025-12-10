FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite
