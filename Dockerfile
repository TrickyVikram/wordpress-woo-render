FROM php:8.2-apache

# Install required packages
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install gd zip mysqli

# Enable mod_rewrite
RUN a2enmod rewrite

# PHP config to support 2GB uploads
RUN echo "upload_max_filesize=2048M\npost_max_size=2048M\nmemory_limit=2048M\nmax_execution_time=600\nmax_input_time=600" > /usr/local/etc/php/conf.d/uploads.ini

# Allow 2GB uploads in Apache
RUN echo "LimitRequestBody 2147483647" >> /etc/apache2/apache2.conf

# Setup /upload directory
RUN mkdir -p /var/www/html/upload && chmod -R 777 /var/www/html/upload

# Copy your file manager
COPY file-manager.php /var/www/html/file-manager.php

# Permissions
RUN chmod -R 777 /var/www/html
