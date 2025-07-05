FROM php:8.2-apache

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install gd zip mysqli

# Enable mod_rewrite
RUN a2enmod rewrite

# Allow directory listing and PHP execution
RUN echo '<Directory /var/www/html>' >> /etc/apache2/apache2.conf && \
    echo '    Options Indexes FollowSymLinks' >> /etc/apache2/apache2.conf && \
    echo '    AllowOverride All' >> /etc/apache2/apache2.conf && \
    echo '    Require all granted' >> /etc/apache2/apache2.conf && \
    echo '</Directory>' >> /etc/apache2/apache2.conf

# Create upload folder
RUN mkdir -p /var/www/html/upload && chmod -R 777 /var/www/html/upload

# Copy your file manager
COPY file-manager.php /var/www/html/file-manager.php

# Set permissions
RUN chmod -R 777 /var/www/html
