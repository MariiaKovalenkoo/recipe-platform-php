FROM php:fpm

RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get update && apt-get install -y git unzip libzip-dev
RUN docker-php-ext-install zip
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Copy Xdebug configuration
COPY ./xdebug.ini "${PHP_INI_DIR}/conf.d"
