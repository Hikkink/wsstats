FROM php:8.2-fpm

# Instalar Nginx
RUN apt-get update && \
    apt-get install -y nginx && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Configurar PHP-FPM
RUN sed -i 's/listen = \/run\/php\/php8.2-fpm.sock/listen = 127.0.0.1:9000/g' /usr/local/etc/php-fpm.d/www.conf

# Crear configuración de Nginx
RUN echo 'server { \
    listen 80; \
    server_name localhost; \
    root /var/www/html; \
    index index.php index.html; \
    client_max_body_size 50M; \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
        include fastcgi_params; \
    } \
}' > /etc/nginx/sites-available/default

# Copiar proyecto
COPY . /var/www/html/

# 👇 CREAR DIRECTORIOS PARA SUBIR ARCHIVOS 👇
RUN mkdir -p /var/www/html/img/series && \
    mkdir -p /var/www/html/uploads && \
    mkdir -p /var/www/html/temp

# 👇 DAR PERMISOS COMPLETOS 👇
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    # Permisos especiales para directorios de subida
    chmod -R 775 /var/www/html/img && \
    chmod -R 775 /var/www/html/uploads && \
    chmod -R 775 /var/www/html/temp && \
    chmod 775 /var/www/html/api && \
    # Crear config.json si no existe
    touch /var/www/html/config.json && \
    chmod 664 /var/www/html/config.json && \
    chown www-data:www-data /var/www/html/config.json

# Configurar PHP para subir archivos grandes
RUN echo "upload_max_filesize = 50M" > /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

# Script de inicio
RUN echo '#!/bin/bash\n\
php-fpm -D\n\
nginx -g "daemon off;"' > /start.sh && chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]