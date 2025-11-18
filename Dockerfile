# PHP 8.3 + Apache
FROM php:8.3-apache

# Instalador de extensiones + Composer
COPY --from=ghcr.io/mlocati/php-extension-installer:latest \
    /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    bcmath bz2 calendar exif gd gettext intl mbstring mysqli pcntl \
    pdo_mysql pdo_pgsql pdo_sqlite pgsql shmop sockets sysvmsg sysvsem \
    sysvshm xsl zip opcache sodium \
    && install-php-extensions @composer

# Apache: módulos para .htaccess y headers/compresión/caché
RUN a2enmod rewrite headers deflate expires

# Permitir .htaccess en DocumentRoot
# Quita el allow-htaccess que tenías
# y usa tu conf
COPY docker/apache/app.conf /etc/apache2/conf-available/app.conf
RUN a2enconf app \
    && sed -i 's/AllowOverride All/AllowOverride None/' /etc/apache2/conf-available/allow-htaccess.conf || true

WORKDIR /var/www/html

# -------- Composer en build (cachea capas) ----------
# Copiamos primero composer.* para aprovechar cache
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction \
    --optimize-autoloader
# ----------------------------------------------------

RUN touch /var/log/php_errors.log && chown www-data:www-data /var/log/php_errors.log
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# Ahora sí, el resto del código
COPY . .

# Permisos base (código legible, dirs escribibles donde toque)
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Entrypoint que genera el .env con envsubst
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh && apache2ctl -t

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
