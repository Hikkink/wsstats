FROM php:8.2-apache-bookworm

# No tocar MPM
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar proyecto
COPY . /var/www/html/

# Si tienes archivos en public/
# RUN mv /var/www/html/* /var/www/html/public/ 2>/dev/null || true

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80