FROM php:8.2-apache

RUN apt-get update && apt-get install -y curl && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    sed -i 's/DirectoryIndex.*/DirectoryIndex index.php index.html/' /etc/apache2/mods-available/dir.conf

COPY apache-config.conf /etc/apache2/sites-available/000-default.conf
COPY railway-entrypoint.sh /usr/local/bin/
COPY healthcheck.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/railway-entrypoint.sh /usr/local/bin/healthcheck.sh

COPY src/ /var/www/html/

RUN a2enmod rewrite && a2ensite 000-default

EXPOSE 80

ENV PORT=80

HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD healthcheck.sh

ENTRYPOINT ["railway-entrypoint.sh"]
