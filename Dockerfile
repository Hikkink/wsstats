FROM php:8.2-apache

# Debug: ver MPM activos
RUN apache2ctl -M | grep mpm

# Instalar extensiones
RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80