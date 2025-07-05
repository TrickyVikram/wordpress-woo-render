FROM wordpress:latest

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install gd zip mysqli

# Copy wp-config.php
COPY wp-config.php /var/www/html/wp-config.php

# Enable directory listing in Apache
RUN echo '<Directory /var/www/html>' >> /etc/apache2/apache2.conf && \
    echo '    Options Indexes FollowSymLinks' >> /etc/apache2/apache2.conf && \
    echo '    AllowOverride All' >> /etc/apache2/apache2.conf && \
    echo '    Require all granted' >> /etc/apache2/apache2.conf && \
    echo '</Directory>' >> /etc/apache2/apache2.conf

# Create upload directory with full permissions
RUN mkdir -p /var/www/html/upload && chmod 777 /var/www/html/upload

# Copy file-manager.php
COPY file-manager.php /var/www/html/file-manager.php

# Optional test file
RUN echo "This is a test file" > /var/www/html/test.txt
