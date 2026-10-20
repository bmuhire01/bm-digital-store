#!/bin/sh
# Starts the shop inside the Docker container.
# Railway (and other hosts) give us the port in $PORT.

set -e

PORT="${PORT:-80}"
DOCROOT="/var/www/html"

echo "Starting BM Digital Store on port ${PORT}..."

# Apache has to listen on the port the platform gave us
sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s!<VirtualHost \*:80>!<VirtualHost *:${PORT}>!" /etc/apache2/sites-available/*.conf

# The uploads folder has to be writable so we can save the product pictures
mkdir -p "${DOCROOT}/assets/uploads"
chown -R www-data:www-data "${DOCROOT}/assets/uploads" 2>/dev/null || true
chmod -R 775 "${DOCROOT}/assets/uploads" 2>/dev/null || true

# Create the database tables the first time we start (it never wipes the data)
php "${DOCROOT}/bin/setup.php"

# Start Apache in the foreground so Docker knows when the container is alive
exec apache2-foreground
