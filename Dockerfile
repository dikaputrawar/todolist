FROM php:8.1-apache

# Install ekstensi mysqli
RUN docker-php-ext-install mysqli
