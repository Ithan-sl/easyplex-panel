#!/bin/bash
set -e

PORT=${PORT:-10000}
echo "Configuring Apache to listen on port ${PORT}..."
sed -i "s/Listen [0-9]*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost \*:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Clear cache and optimize
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

exec apache2-foreground
