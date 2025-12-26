#!/bin/bash
# Script to update Apache configuration for 6ammart deployment
# Run this script on the server with sudo privileges

set -e

APACHE_CONFIG="/etc/apache2/sites-available/6ammart.conf"
BACKUP_CONFIG="${APACHE_CONFIG}.backup.$(date +%Y%m%d_%H%M%S)"

echo "Backing up current Apache config to ${BACKUP_CONFIG}..."
sudo cp "${APACHE_CONFIG}" "${BACKUP_CONFIG}"

echo "Updating Apache configuration..."
sudo tee "${APACHE_CONFIG}" > /dev/null << 'APACHE_EOF'
<VirtualHost *:80>
    ServerName 193.162.129.214
    DocumentRoot /var/www/6ammart-laravel/public

    <Directory /var/www/6ammart-laravel/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost/"
    </FilesMatch>

    ProxyPreserveHost On

    # Exclude /api from proxying - serve directly from Laravel
    ProxyPass /api !
    
    # Proxy all other requests to React app
    ProxyPass / http://127.0.0.1:3000/
    ProxyPassReverse / http://127.0.0.1:3000/

    ErrorLog ${APACHE_LOG_DIR}/6ammart_error.log
    CustomLog ${APACHE_LOG_DIR}/6ammart_access.log combined
</VirtualHost>
APACHE_EOF

echo "Apache configuration updated successfully!"
echo "Backup saved to: ${BACKUP_CONFIG}"

