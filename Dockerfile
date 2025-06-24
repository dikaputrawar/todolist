FROM php:8.1-apache

# Install mysqli
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Aktifkan mod_rewrite (kalau kamu perlu routing)
RUN a2enmod rewrite
