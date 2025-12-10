FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    sed -i 's/DirectoryIndex.*/DirectoryIndex index.php index.html/' /etc/apache2/mods-available/dir.conf

COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

COPY src/ /var/www/html/

RUN a2enmod rewrite && a2ensite 000-default
