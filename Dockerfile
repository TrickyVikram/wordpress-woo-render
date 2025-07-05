FROM wordpress:latest

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install gd zip mysqli

# Enable directory listing and allow .php editing
RUN echo '<Directory /var/www/html>' >> /etc/apache2/apache2.conf && \
    echo '    Options Indexes FollowSymLinks' >> /etc/apache2/apache2.conf && \
    echo '    AllowOverride All' >> /etc/apache2/apache2.conf && \
    echo '    Require all granted' >> /etc/apache2/apache2.conf && \
    echo '</Directory>' >> /etc/apache2/apache2.conf

# Create /upload directory and set permissions
RUN mkdir -p /var/www/html/upload && chmod -R 777 /var/www/html/upload

# Copy WordPress config and file manager
COPY wp-config.php /var/www/html/wp-config.php
COPY file-manager.php /var/www/html/file-manager.php

# Set permissions for editing
RUN chmod -R 777 /var/www/html

# Enable Apache rewrite (optional for .htaccess usage)
RUN a2enmod rewrite
