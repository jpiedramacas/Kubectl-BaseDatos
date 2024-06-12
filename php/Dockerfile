# Use an official PHP Apache runtime 
FROM php:8.2-apache

# Enable Apache modules 
RUN a2enmod rewrite

# Install PostgreSQL client and its PHP extensions 
RUN apt-get update \
    && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Install MySQLi extension 
RUN docker-php-ext-install mysqli

# Set the working directory to /var/www/html 
WORKDIR /var/www/html

# Copy the PHP code from the ./html directory to /var/www/html in the container 
COPY html/ /var/www/html/

# Expose port 80 
EXPOSE 80
