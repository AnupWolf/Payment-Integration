# Use official PHP Apache image
FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install system dependencies and PHP extensions required for Laravel
RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libzip-dev \
        libonig-dev \
        libxml2-dev \
        zlib1g-dev \
        libssl-dev \
        libcurl4-openssl-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        build-essential \
        pkg-config \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo pdo_mysql zip mbstring tokenizer xml \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html

# Copy .env.example to .env if missing (do not run artisan during build)
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Set permissions for Laravel folders
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Set unlimited memory for Composer (avoid memory issues in CI)
ENV COMPOSER_MEMORY_LIMIT=-1

# Install Composer dependencies without running scripts (safe for CI/CD)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Set Apache document root to Laravel public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Expose Apache port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]