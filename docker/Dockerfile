# BM Digital Store - Docker image
#
# Railway (and most other Docker hosts) build this image and run it.
# The app itself needs no changes, it is the same code as on XAMPP.

FROM php:8.2-apache

# We use the mysqli_* functions, so that extension has to be there
RUN docker-php-ext-install mysqli

# The .htaccess uses mod_rewrite for the links without the .php
RUN a2enmod rewrite

# The site itself
COPY . /var/www/html/

# The start script sets the port, the permissions and prepares the database
RUN chmod +x /var/www/html/docker/start.sh

# The platform tells us which port to use (in $PORT)
EXPOSE 80

CMD ["/var/www/html/docker/start.sh"]
