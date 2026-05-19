FROM php:8.4-fpm

# Get Host User ID (usually 1000)
ARG uid=1000
ARG user=ubuntu

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip \
    && docker-php-ext-install pdo pdo_mysql

# Create system user to match host (Solves Permissions)
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && chown -R $user:$user /home/$user

WORKDIR /var/www/html
# USER $user