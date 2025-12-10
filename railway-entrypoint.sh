#!/bin/bash
set -e

PORT=${PORT:-80}

echo "Configuring Apache for Railway (PORT=$PORT)..."

sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/Listen \[::\]:80/Listen \[::\]:$PORT/" /etc/apache2/ports.conf || true

sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf

echo "Starting Apache on port $PORT..."
exec apache2-foreground

