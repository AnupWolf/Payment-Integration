# Use official PHP Apache image
FROM php:8.2-apache

# Install dependencies
RUN docker-php-ext-install pdo pdo_mysql

# Enable mod_rewrite for Laravel or routing
RUN a2enmod rewrite

# Copy project files to Apache root
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Set permissions (optional, for Laravel storage)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]