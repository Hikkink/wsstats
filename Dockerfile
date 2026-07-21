FROM php:8.2-fpm

# Instalar Nginx
RUN apt-get update && \
    apt-get install -y nginx && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Crear configuración de Nginx
RUN echo 'server { \
    listen 80; \
    server_name localhost; \
    root /var/www/html; \
    index index.php index.html; \
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

# Script de inicio
RUN echo '#!/bin/bash\n\
nginx\n\
php-fpm' > /start.sh && chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]