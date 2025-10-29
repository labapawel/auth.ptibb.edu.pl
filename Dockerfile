### Production-ready multi-stage Dockerfile
### Builder stage: install dev tools, composer and node, build assets
FROM php:8.2-cli AS builder
WORKDIR /var/www/html

# system deps for building PHP extensions and node builds
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libldap2-dev \
    ca-certificates \
    nodejs \
    npm \
    && rm -rf /var/lib/apt/lists/*

# Composer binary from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy app sources and install PHP & JS deps
COPY . /var/www/html
# Enable building required PHP extensions (ldap) in builder so composer can satisfy ext requirements
RUN docker-php-ext-install ldap || true

# Avoid 'dubious ownership' errors when building in Docker context
RUN git config --global --add safe.directory /var/www/html || true

# Install PHP dependencies (production) and build assets
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
RUN npm ci && npm run build

### Final stage: minimal runtime image with PHP-FPM
FROM php:8.2-fpm
WORKDIR /var/www/html

# Install only runtime deps required for PHP extensions (LDAP, GD, etc.)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libldap2-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip ldap

# Copy built application from builder
COPY --from=builder /var/www/html /var/www/html

# Ensure permissions for runtime (www-data)
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type f -exec chmod 644 {} + \
    && find /var/www/html -type d -exec chmod 755 {} + \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy start script and make executable
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Run as non-root user for improved security
USER www-data

# Expose php-fpm port to other services (not to host)
EXPOSE 9000

# Start the container with the start script (it will launch php-fpm)
CMD ["/usr/local/bin/start.sh"]