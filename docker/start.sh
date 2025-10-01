#!/bin/bash

# Generate application key if not exists
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Generate app key if empty
if ! grep -q "APP_KEY=base64:" /var/www/html/.env; then
    php artisan key:generate --force
fi

# Clear and cache config
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Set permissions again (in case of volume mounts)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Start Apache in foreground
apache2-foreground