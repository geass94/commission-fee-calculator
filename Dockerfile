FROM php:8.2-fpm

# Update and install necessary packages
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/* \
    && curl -sLS https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm


# Install PHP extensions
RUN docker-php-ext-install pdo_mysql zip intl mbstring exif pcntl bcmath gd
RUN docker-php-ext-enable pdo_mysql zip intl mbstring exif pcntl bcmath gd

WORKDIR /var/www
# Copy existing application directory contents
COPY . /var/www

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www:www . /var/www

# Change current user to www
USER www

EXPOSE 9000