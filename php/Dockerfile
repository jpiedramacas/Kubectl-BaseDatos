# Dockerfile
FROM php:7.4-apache

# Copiar y instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalar extensiones necesarias
RUN docker-php-ext-install mysqli

# Habilitar módulos necesarios de Apache
RUN a2enmod rewrite

# Copiar los archivos del proyecto al contenedor
COPY . /var/www/html/

# Instalar dependencias de Composer dentro del contenedor
RUN composer install --no-dev --optimize-autoloader --working-dir=/var/www/html/

# Exponer el puerto 80
EXPOSE 80
