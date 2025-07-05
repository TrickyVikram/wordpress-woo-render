FROM wordpress:latest

# PHP extensions install karo (WooCommerce ke liye mysqli zaroori hai)
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Optional: Extra libraries for image & zip handling
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev zip unzip \
    && docker-php-ext-install gd zip
