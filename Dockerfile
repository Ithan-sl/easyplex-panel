FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by Laravel & EasyPlex
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache DocumentRoot to /var/www/html/public with AllowOverride All
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && printf '\n<Directory /var/www/html/public>\n    Options Indexes FollowSymLinks\n    AllowOverride All\n    Require all granted\n</Directory>\n' >> /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Download and extract vendor dependencies
RUN curl -sL https://github.com/Ithan-sl/easyplex-panel/releases/download/v1.0/vendor.zip -o /var/www/html/vendor.zip \
    && unzip -q /var/www/html/vendor.zip -d /var/www/html/ \
    && rm /var/www/html/vendor.zip

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/vendor \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 10000 8080 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
