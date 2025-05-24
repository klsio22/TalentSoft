FROM php:8.3.4-fpm

# Instalar dependências do sistema e extensões PHP
RUN apt-get update && apt-get install -y \
  curl \
  libzip-dev \
  unzip \
  zip \
  && docker-php-ext-install pdo pdo_mysql zip \
  && docker-php-ext-enable pdo_mysql zip \
  && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
  && rm -rf /var/lib/apt/lists/*
