# LET'S MANU SERVICES — image de développement local
# PHP 8.3 + Apache : le même type de serveur que l'hébergement mutualisé Hostinger,
# ce qui permet de tester le site avec son .htaccess réel (réécriture, blocages, cache).

FROM php:8.3-apache

# Extensions nécessaires : GD (JPEG, PNG, WebP) pour les photos, EXIF pour redresser les photos de téléphone.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libjpeg62-turbo-dev libpng-dev libwebp-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install -j"$(nproc)" gd exif \
    && a2enmod rewrite expires headers deflate \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini /usr/local/etc/php/conf.d/letsmanu.ini

# Copie du site (en développement, docker-compose.yml monte le dossier du projet par-dessus,
# pour que chaque modification de fichier soit visible immédiatement).
COPY . /var/www/html/
RUN mkdir -p /var/www/html/data /var/www/html/uploads/site /var/www/html/uploads/realisations \
    && chown -R www-data:www-data /var/www/html/data /var/www/html/uploads

EXPOSE 80
