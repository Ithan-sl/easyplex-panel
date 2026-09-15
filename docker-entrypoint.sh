#!/bin/bash
set -e

PORT=${PORT:-10000}
echo "Configuring Apache to listen on port ${PORT}..."
sed -i "s/Listen [0-9]*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost \*:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Generate Passport encryption keys if missing
if [ ! -f /var/www/html/storage/oauth-private.key ]; then
    echo "Generating Passport OAuth keys..."
    php artisan passport:keys --force || true
fi

chmod 600 /var/www/html/storage/oauth-private.key 2>/dev/null || true
chmod 660 /var/www/html/storage/oauth-public.key 2>/dev/null || true
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Clear cache and optimize
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

exec apache2-foreground
