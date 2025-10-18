FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libssl-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libcurl4-openssl-dev \
    pkg-config \
    libprotobuf-dev \
    protobuf-compiler \
    libicu-dev \
    autoconf \
    zlib1g-dev 
    #php-dev \
    #php-pear

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd xml zip soap

# Install raphf extension
RUN pecl install raphf && docker-php-ext-enable raphf

# Install ext-http extension
RUN pecl install pecl_http && docker-php-ext-enable http

# Instala grpc versión más ligera y estable
RUN pecl install grpc-1.55.0 && docker-php-ext-enable grpc

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Configure git safe.directory (for the ownership issue)
RUN git config --global --add safe.directory /var/www

# Install dependencies
RUN composer install --ignore-platform-reqs

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_16.x | bash -
RUN apt-get update && apt-get install -y nodejs

# Install project dependencies and build assets
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www