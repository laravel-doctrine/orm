# Use official PHP 8.1 CLI image as base
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    && docker-php-ext-install zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Set working directory
WORKDIR /var/www/html/laravel-doctrine-orm

# Install Laravel dependencies
RUN composer install --no-interaction --optimize-autoloader --dev || true

# Expose port for Laravel development server
EXPOSE 8000

# Start Laravel development server
CMD ["apache2-foreground"]
