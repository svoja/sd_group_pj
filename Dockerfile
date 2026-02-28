FROM php:8.2-apache

# ติดตั้ง mysqli
RUN docker-php-ext-install mysqli