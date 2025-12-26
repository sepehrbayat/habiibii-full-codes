#!/bin/bash
# Final deployment script - upload this to server and run: sudo bash final-deployment.sh

set -e

CONTROLLERS_DIR="/var/www/6ammart-laravel/Modules/Gateways/Http/Controllers"

echo "=========================================="
echo "6ammart Complete Deployment Fix"
echo "=========================================="
echo ""

# Update all controllers using the fixed versions
echo "Step 1: Updating payment gateway controllers..."

# The controllers will be updated via SCP separately
# This script handles Apache and Laravel configuration

# Step 2: Update Apache Configuration
echo "Step 2: Updating Apache configuration..."
APACHE_CONFIG="/etc/apache2/sites-available/6ammart.conf"
BACKUP_CONFIG="${APACHE_CONFIG}.backup.$(date +%Y%m%d_%H%M%S)"

if [ -f "${APACHE_CONFIG}" ]; then
    cp "${APACHE_CONFIG}" "${BACKUP_CONFIG}"
    echo "Backed up config to ${BACKUP_CONFIG}"
fi

cat > "${APACHE_CONFIG}" << 'APACHE_EOF'
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

echo "✓ Apache configuration updated"

# Step 3: Enable Apache Modules
echo "Step 3: Enabling Apache modules..."
a2enmod rewrite proxy proxy_http 2>/dev/null || true
echo "✓ Modules enabled"

# Step 4: Verify Apache Config
echo "Step 4: Verifying Apache config..."
if apache2ctl configtest; then
    echo "✓ Config valid"
else
    echo "✗ Config has errors!"
    exit 1
fi

# Step 5: Reload Apache
echo "Step 5: Reloading Apache..."
systemctl reload apache2
echo "✓ Apache reloaded"

# Step 6: Clear Laravel Caches
echo "Step 6: Clearing Laravel caches..."
cd /var/www/6ammart-laravel
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo "✓ Caches cleared"

# Step 7: Verify Routes
echo "Step 7: Verifying routes..."
if timeout 20 php artisan route:list 2>&1 | head -n 30 > /dev/null; then
    echo "✓ Routes load successfully"
    echo "Sample routes:"
    php artisan route:list 2>&1 | head -n 5
else
    echo "✗ Routes failed to load!"
    php artisan route:list 2>&1 | head -n 20
    exit 1
fi

# Step 8: Verify Services
echo "Step 8: Verifying services..."
systemctl is-active mysql && echo "✓ MySQL running" || (systemctl start mysql && systemctl enable mysql && echo "✓ MySQL started")
systemctl is-active apache2 && echo "✓ Apache running" || (echo "✗ Apache not running!" && exit 1)
pm2 list | grep -q "6ammart-react" && echo "✓ React app running" || echo "⚠ React app not in PM2"

# Step 9: Test API
echo "Step 9: Testing API..."
API_TEST=$(curl -s -w "\n%{http_code}" -X POST http://127.0.0.1/api/v1/auth/login \
    -H "Content-Type: application/json" \
    -d '{"email":"test@example.com","password":"test"}' 2>/dev/null || echo -e "\n000")
HTTP_CODE=$(echo "$API_TEST" | tail -n 1)
if [ "$HTTP_CODE" != "000" ] && [ "$HTTP_CODE" != "404" ]; then
    echo "✓ API responded (HTTP $HTTP_CODE)"
else
    echo "⚠ API test: HTTP $HTTP_CODE"
fi

echo ""
echo "=========================================="
echo "Deployment completed!"
echo "=========================================="

