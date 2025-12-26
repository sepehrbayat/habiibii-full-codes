#!/bin/bash
# Complete deployment fix script for 6ammart server
# Run this script on the server with appropriate privileges

set -e

echo "=========================================="
echo "6ammart Deployment Fix Script"
echo "=========================================="
echo ""

# Step 1: Update Apache Configuration
echo "Step 1: Updating Apache configuration..."
APACHE_CONFIG="/etc/apache2/sites-available/6ammart.conf"
BACKUP_CONFIG="${APACHE_CONFIG}.backup.$(date +%Y%m%d_%H%M%S)"

if [ -f "${APACHE_CONFIG}" ]; then
    echo "Backing up current Apache config to ${BACKUP_CONFIG}..."
    sudo cp "${APACHE_CONFIG}" "${BACKUP_CONFIG}"
fi

echo "Writing new Apache configuration..."
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

echo "✓ Apache configuration updated"
echo ""

# Step 2: Enable Required Apache Modules
echo "Step 2: Enabling required Apache modules..."
sudo a2enmod rewrite proxy proxy_http 2>/dev/null || true
echo "✓ Apache modules enabled"
echo ""

# Step 3: Verify Apache Config Syntax
echo "Step 3: Verifying Apache configuration syntax..."
if sudo apache2ctl configtest; then
    echo "✓ Apache configuration syntax is valid"
else
    echo "✗ Apache configuration has syntax errors!"
    exit 1
fi
echo ""

# Step 4: Reload Apache
echo "Step 4: Reloading Apache..."
sudo systemctl reload apache2
echo "✓ Apache reloaded"
echo ""

# Step 5: Clear Laravel Caches
echo "Step 5: Clearing Laravel caches..."
cd /var/www/6ammart-laravel
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo "✓ Laravel caches cleared"
echo ""

# Step 6: Verify Routes Load
echo "Step 6: Verifying Laravel routes load..."
if timeout 20 php artisan route:list 2>&1 | head -n 30 > /dev/null; then
    echo "✓ Laravel routes load successfully"
else
    echo "✗ Laravel routes failed to load!"
    exit 1
fi
echo ""

# Step 7: Verify Services
echo "Step 7: Verifying services..."
echo "Checking MySQL..."
if systemctl is-active --quiet mysql; then
    echo "✓ MySQL is running"
else
    echo "⚠ MySQL is not running, attempting to start..."
    sudo systemctl start mysql
    sudo systemctl enable mysql
fi

echo "Checking Apache..."
if systemctl is-active --quiet apache2; then
    echo "✓ Apache is running"
else
    echo "✗ Apache is not running!"
    exit 1
fi

echo "Checking React app (PM2)..."
if pm2 list | grep -q "6ammart-react"; then
    echo "✓ React app is running in PM2"
else
    echo "⚠ React app is not running in PM2"
fi
echo ""

# Step 8: Test API Endpoint
echo "Step 8: Testing API endpoint..."
API_RESPONSE=$(curl -s -w "\n%{http_code}" http://127.0.0.1/api/v1/auth/login -X POST \
    -H "Content-Type: application/json" \
    -d '{"email":"test@example.com","password":"test"}' 2>/dev/null || echo -e "\n000")

HTTP_CODE=$(echo "$API_RESPONSE" | tail -n 1)
BODY=$(echo "$API_RESPONSE" | head -n -1)

if [ "$HTTP_CODE" != "000" ]; then
    echo "✓ API endpoint responded (HTTP $HTTP_CODE)"
    if echo "$BODY" | grep -q "json\|error\|message"; then
        echo "✓ Response appears to be JSON"
    else
        echo "⚠ Response may not be JSON format"
    fi
else
    echo "⚠ API endpoint test failed (connection error)"
fi
echo ""

# Step 9: Test React App
echo "Step 9: Testing React app..."
REACT_RESPONSE=$(curl -s -w "\n%{http_code}" http://127.0.0.1:3000/ 2>/dev/null || echo -e "\n000")
REACT_CODE=$(echo "$REACT_RESPONSE" | tail -n 1)

if [ "$REACT_CODE" = "200" ]; then
    echo "✓ React app is accessible on port 3000"
else
    echo "⚠ React app may not be running on port 3000"
fi
echo ""

echo "=========================================="
echo "Deployment fix completed!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Sync fixed controller files to server:"
echo "   scp Modules/Gateways/Http/Controllers/{SenangPay,Phonepe,FlutterwaveV3,WorldPay}Controller.php sepehrbyt:/var/www/6ammart-laravel/Modules/Gateways/Http/Controllers/"
echo ""
echo "2. After syncing controllers, run on server:"
echo "   cd /var/www/6ammart-laravel"
echo "   php artisan route:clear config:clear cache:clear view:clear"
echo ""
echo "3. Test the deployment:"
echo "   curl -v http://193.162.129.214/api/v1/auth/login"
echo ""

