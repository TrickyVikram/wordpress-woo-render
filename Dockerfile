FROM wordpress:latest

# Update package list and install required libraries
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install gd zip mysqli

# Copy custom wp-config.php into the container
COPY wp-config.php /var/www/html/wp-config.php
