# Use a more complete PHP image (has most extensions prebuilt)
FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install only minimal required tools (NO heavy compilation)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project
COPY . .

# Ensure .env exists
RUN cp .env.example .env || true

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 storage bootstrap/cache || true

# Avoid memory issues
ENV COMPOSER_MEMORY_LIMIT=-1

# Install dependencies (safe mode)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Set Apache root to Laravel public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

EXPOSE 80

CMD ["apache2-foreground"]