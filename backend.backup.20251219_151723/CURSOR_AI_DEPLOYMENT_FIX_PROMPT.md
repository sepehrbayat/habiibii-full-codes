# 🚨 Critical Deployment Issues - Complete Fix Guide

## Current Situation

We have a Laravel backend and React (Next.js) frontend deployed on a Ubuntu 24.04 server (IP: `193.162.129.214`). The deployment is **partially working** but has critical issues preventing the Laravel API from functioning correctly.

## Architecture Overview

- **Laravel Backend**: `/var/www/6ammart-laravel/` - Serves API endpoints at `/api/*`
- **React Frontend**: `/var/www/6ammart-react/` - Next.js app running on port 3000 via PM2
- **Web Server**: Apache 2.4.58 with PHP 8.2-FPM
- **Database**: MariaDB 10.11.13
- **Apache Config**: `/etc/apache2/sites-available/6ammart.conf`

## Critical Issues to Fix

### Issue #1: Payment Gateway Controllers - Uninitialized Properties

**Problem**: Multiple payment gateway controllers have uninitialized `$config_values` or `$values` properties, causing Laravel routes to fail loading with errors like:
```
Typed property Modules\Gateways\Http\Controllers\{Controller}::$config_values must not be accessed before initialization
```

**Affected Controllers** (need verification and fixing):
- `CCavenueController.php` ✅ (FIXED)
- `StripePaymentController.php` ✅ (FIXED)
- `PvitController.php` - NEEDS FIX
- `MoncashController.php` - NEEDS FIX
- `WorldPayController.php` - NEEDS FIX (has syntax errors)
- `PayFastController.php` - NEEDS FIX
- `VivaWalletController.php` - NEEDS FIX
- `MomoPayController.php` - NEEDS FIX
- `MaxiCashController.php` - NEEDS FIX
- `SenangPayController.php` - NEEDS FIX
- `PaymobController.php` - NEEDS FIX
- `PhonepeController.php` - NEEDS FIX
- `FlutterwaveV3Controller.php` - NEEDS FIX
- Any other controllers in `Modules/Gateways/Http/Controllers/` that use `$config_values` or `$values`

**Fix Pattern**:
1. Find all controllers with `$config_values` or `$values` property
2. In the constructor, initialize the property to `null` BEFORE any conditional assignment
3. Add a check `if ($config && $this->config_values)` before accessing properties
4. Ensure proper syntax (no duplicate braces, proper opening/closing)

**Example Fix**:
```php
public function __construct(PaymentRequest $payment)
{
    $config = $this->payment_config('gateway_name', 'payment_config');
    $this->config_values = null; // Initialize $config_values
    if (!is_null($config) && $config->mode == 'live') {
        $this->config_values = json_decode($config->live_values);
    } elseif (!is_null($config) && $config->mode == 'test') {
        $this->config_values = json_decode($config->test_values);
    }

    if ($config && $this->config_values) { // Check if config_values is set
        $this->property1 = $this->config_values->property1;
        $this->property2 = $this->config_values->property2;
    }

    $this->payment = $payment;
}
```

**Verification**:
```bash
cd /var/www/6ammart-laravel
php artisan route:list 2>&1 | head -n 20
# Should show routes without errors
```

---

### Issue #2: Apache Configuration - API Routing

**Problem**: Requests to `/api/*` endpoints are being routed to the React app (port 3000) instead of being handled by Laravel. This causes 404 errors with `X-Powered-By: Next.js` header.

**Current Apache Config** (`/etc/apache2/sites-available/6ammart.conf`):
```apache
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

    # Enable rewrite engine
    RewriteEngine On

    # Route /api requests to Laravel (no proxy, serve directly)
    RewriteCond %{REQUEST_URI} ^/api
    RewriteRule ^(.*)$ - [L]

    # Proxy all other requests to React app
    RewriteCond %{REQUEST_URI} !^/api
    RewriteRule ^(.*)$ http://127.0.0.1:3000/$1 [P,L]
    ProxyPassReverse / http://127.0.0.1:3000/

    ErrorLog ${APACHE_LOG_DIR}/6ammart_error.log
    CustomLog ${APACHE_LOG_DIR}/6ammart_access.log combined
</VirtualHost>
```

**Required Fix**:
The Apache configuration needs to:
1. Serve `/api/*` requests directly from Laravel (no proxying)
2. Proxy all other requests (`/`, `/login`, etc.) to React app on port 3000
3. Ensure `mod_rewrite` and `mod_proxy` are enabled

**Recommended Apache Config**:
```apache
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
```

**Verification Steps**:
```bash
# 1. Check Apache modules are enabled
a2enmod rewrite proxy proxy_http
systemctl reload apache2

# 2. Test Laravel API endpoint
curl -v http://127.0.0.1/api/v1/auth/login -X POST \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test"}'
# Should return JSON response from Laravel, NOT HTML from Next.js

# 3. Check response headers
curl -I http://127.0.0.1/api/v1/auth/login
# Should NOT have "X-Powered-By: Next.js" header
```

---

### Issue #3: Laravel Routes Not Loading

**Problem**: `php artisan route:list` fails due to controller errors, preventing routes from being registered.

**Root Cause**: Uninitialized properties in payment gateway controllers (see Issue #1)

**Fix Steps**:
1. Fix all payment gateway controllers (Issue #1)
2. Clear all Laravel caches:
   ```bash
   cd /var/www/6ammart-laravel
   php artisan route:clear
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```
3. Verify routes load:
   ```bash
   php artisan route:list 2>&1 | head -n 30
   # Should show routes without errors
   ```

---

### Issue #4: MySQL Service Status

**Problem**: MySQL service may stop, causing database connection errors.

**Fix**:
```bash
systemctl status mysql
systemctl start mysql
systemctl enable mysql
```

**Verification**:
```bash
mysql -u 6ammart -p'H6ammart_DB!' -e "SELECT 1;" 6ammart_db
```

---

### Issue #5: React App Status

**Problem**: React app must be running on port 3000 for Apache proxy to work.

**Current Status**: Running via PM2 (process name: `6ammart-react`)

**Verification**:
```bash
pm2 list
curl -s http://127.0.0.1:3000/ | head -n 1
# Should return HTML from Next.js
```

**If Not Running**:
```bash
cd /var/www/6ammart-react
pm2 start npm --name "6ammart-react" -- start
pm2 save
pm2 startup
```

---

## Complete Fix Workflow

### Step 1: Fix All Payment Gateway Controllers

```bash
# 1. Find all controllers with uninitialized properties
cd /home/sepehr/Projects/6ammart-laravel
find Modules/Gateways/Http/Controllers -name "*Controller.php" -type f | \
  xargs grep -l "\$this->config_values" | \
  xargs grep -L "\$this->config_values = null"

# 2. For each controller found:
#    - Add `$this->config_values = null;` at the start of constructor
#    - Change `if ($config) {` to `if ($config && $this->config_values) {`
#    - Fix any syntax errors (duplicate braces, missing braces)

# 3. Verify syntax
find Modules/Gateways/Http/Controllers -name "*Controller.php" -type f -exec php -l {} \;

# 4. Upload fixed files to server
find Modules/Gateways/Http/Controllers -name "*Controller.php" -type f -exec \
  scp {} sepehrbyt:/var/www/6ammart-laravel/{} \;
```

### Step 2: Fix Apache Configuration

```bash
# 1. Update Apache config on server
ssh sepehrbyt << 'EOF'
cat > /etc/apache2/sites-available/6ammart.conf << 'APACHE_EOF'
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

# 2. Enable required modules
a2enmod rewrite proxy proxy_http

# 3. Reload Apache
systemctl reload apache2

# 4. Verify config syntax
apache2ctl configtest
EOF
```

### Step 3: Clear Laravel Caches and Verify

```bash
ssh sepehrbyt << 'EOF'
cd /var/www/6ammart-laravel

# Clear all caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Verify routes load
timeout 20 php artisan route:list 2>&1 | head -n 30

# Test API endpoint
curl -s http://127.0.0.1/api/v1/auth/login -X POST \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test"}' | head -n 20
EOF
```

### Step 4: Verify Services

```bash
ssh sepehrbyt << 'EOF'
# Check MySQL
systemctl status mysql | head -n 5

# Check Apache
systemctl status apache2 | head -n 5

# Check React app
pm2 list

# Check React app is accessible
curl -s http://127.0.0.1:3000/ | head -n 1
EOF
```

---

## Testing Checklist

After fixes, verify:

- [ ] `php artisan route:list` runs without errors
- [ ] `curl http://127.0.0.1/api/v1/auth/login` returns JSON (not HTML)
- [ ] Response does NOT have `X-Powered-By: Next.js` header
- [ ] React app is accessible at `http://193.162.129.214/`
- [ ] Laravel API is accessible at `http://193.162.129.214/api/v1/auth/login`
- [ ] No syntax errors in any payment gateway controllers
- [ ] MySQL service is running
- [ ] Apache service is running
- [ ] React app (PM2) is running

---

## Server Access Information

- **IP**: `193.162.129.214`
- **User**: `root`
- **SSH Host**: `sepehrbyt` (configured in `~/.ssh/config`)
- **SSH Command**: `ssh sepehrbyt`
- **Note**: Uses SSH key authentication via SSH config

---

## Expected Final State

1. **Laravel API**: All routes load correctly, `/api/*` endpoints return JSON responses
2. **React Frontend**: Accessible at root URL, handles all non-API requests
3. **Apache**: Correctly routes `/api/*` to Laravel, everything else to React
4. **Controllers**: All payment gateway controllers have properly initialized properties
5. **Services**: MySQL, Apache, and PM2 (React) all running

---

## Debugging Commands

```bash
# Check Apache error logs
tail -n 50 /var/log/apache2/6ammart_error.log

# Check Laravel logs
tail -n 50 /var/www/6ammart-laravel/storage/logs/laravel.log

# Test API endpoint with verbose output
curl -v http://127.0.0.1/api/v1/auth/login -X POST \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test"}'

# Check if React app is responding
curl -I http://127.0.0.1:3000/

# Check Apache configuration
apache2ctl -S

# List all Laravel routes
cd /var/www/6ammart-laravel && php artisan route:list
```

---

## Notes

- All fixes should be applied to BOTH local files (`/home/sepehr/Projects/6ammart-laravel/`) and server files (`/var/www/6ammart-laravel/`)
- After fixing controllers, always clear Laravel caches
- After changing Apache config, always reload Apache
- Test each fix incrementally before moving to the next
- Keep backups of working configurations before making changes

---

**Priority Order**:
1. Fix all payment gateway controllers (blocks route loading)
2. Fix Apache configuration (blocks API access)
3. Verify all services are running
4. Test end-to-end functionality

