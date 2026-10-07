FROM php:8.2-apache

# ដំឡើង extensions ដែលត្រូវការ
RUN apt-get update && apt-get install -y \
        libzip-dev \
        zip \
        unzip \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        mysqli \
        pdo \
        pdo_mysql \
        gd \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# បើក mod_rewrite សម្រាប់ .htaccess
RUN a2enmod rewrite

# កំណត់ working directory
WORKDIR /var/www/html

# កំណត់សិទ្ធិ
RUN chown -R www-data:www-data /var/www/html
