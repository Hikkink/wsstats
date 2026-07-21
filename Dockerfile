FROM php:8.2-apache

# Desactivar MPM conflictivo y activar el correcto para PHP
RUN a2dismod mpm_event && a2enmod mpm_prefork

# Instalar extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar tu proyecto al servidor Apache
COPY . /var/www/html/

# Exponer el puerto para Railway
EXPOSE 80
