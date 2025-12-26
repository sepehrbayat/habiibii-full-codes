# Deployment Fix Instructions

This document provides step-by-step instructions to apply all deployment fixes to the server.

## Prerequisites

- SSH access to server (193.162.129.214)
- Sudo privileges on server
- Local files have been fixed (controllers)

## Fixed Files

The following controller files have been fixed locally and need to be synced to the server:

1. `Modules/Gateways/Http/Controllers/SenangPayController.php`
2. `Modules/Gateways/Http/Controllers/PhonepeController.php`
3. `Modules/Gateways/Http/Controllers/FlutterwaveV3Controller.php`
4. `Modules/Gateways/Http/Controllers/WorldPayController.php`

## Deployment Steps

### Step 1: Sync Fixed Controllers to Server

From your local machine, sync the fixed controllers to the server:

```bash
cd /home/sepehr/Projects/6ammart-laravel

# Sync all fixed controllers
scp Modules/Gateways/Http/Controllers/SenangPayController.php sepehrbyt:/var/www/6ammart-laravel/Modules/Gateways/Http/Controllers/
scp Modules/Gateways/Http/Controllers/PhonepeController.php sepehrbyt:/var/www/6ammart-laravel/Modules/Gateways/Http/Controllers/
scp Modules/Gateways/Http/Controllers/FlutterwaveV3Controller.php sepehrbyt:/var/www/6ammart-laravel/Modules/Gateways/Http/Controllers/
scp Modules/Gateways/Http/Controllers/WorldPayController.php sepehrbyt:/var/www/6ammart-laravel/Modules/Gateways/Http/Controllers/
```

### Step 2: Run Deployment Script on Server

SSH to the server and run the deployment script:

```bash
ssh sepehrbyt

# Copy the deployment script to server (or create it there)
# Then run:
sudo bash deploy-fixes-to-server.sh
```

Alternatively, you can run the steps manually:

#### 2.1: Update Apache Configuration

```bash
sudo cp /etc/apache2/sites-available/6ammart.conf /etc/apache2/sites-available/6ammart.conf.backup

sudo tee /etc/apache2/sites-available/6ammart.conf > /dev/null << 'EOF'
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
EOF
```

#### 2.2: Enable Apache Modules

```bash
sudo a2enmod rewrite proxy proxy_http
```

#### 2.3: Verify Apache Config

```bash
sudo apache2ctl configtest
```

#### 2.4: Reload Apache

```bash
sudo systemctl reload apache2
```

#### 2.5: Clear Laravel Caches

```bash
cd /var/www/6ammart-laravel
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

#### 2.6: Verify Routes Load

```bash
cd /var/www/6ammart-laravel
php artisan route:list 2>&1 | head -n 30
```

Should show routes without errors.

#### 2.7: Verify Services

```bash
# Check MySQL
systemctl status mysql
sudo systemctl start mysql  # if not running
sudo systemctl enable mysql

# Check Apache
systemctl status apache2

# Check React app
pm2 list
```

### Step 3: Test Deployment

#### 3.1: Test API Endpoint

```bash
curl -v http://127.0.0.1/api/v1/auth/login -X POST \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test"}'
```

**Expected:**
- Response should be JSON (not HTML)
- Should NOT have `X-Powered-By: Next.js` header
- Should return error message about invalid credentials (which is expected)

#### 3.2: Test React App

```bash
curl -I http://127.0.0.1:3000/
```

Should return HTTP 200.

#### 3.3: Test from External

Access in browser:
- `http://193.162.129.214/` - Should show React app
- `http://193.162.129.214/api/v1/auth/login` - Should return JSON response

## Verification Checklist

After deployment, verify:

- [ ] All payment gateway controllers have proper initialization
- [ ] No syntax errors in any controller files
- [ ] Apache config excludes `/api` from proxying
- [ ] Apache modules (rewrite, proxy, proxy_http) are enabled
- [ ] Laravel routes load without errors
- [ ] MySQL service is running
- [ ] Apache service is running
- [ ] React app (PM2) is running
- [ ] API endpoints return JSON (not HTML)
- [ ] React app is accessible at root URL

## Troubleshooting

### Routes Still Not Loading

If `php artisan route:list` still fails:

1. Check for other controllers with uninitialized properties:
   ```bash
   cd /var/www/6ammart-laravel
   find Modules/Gateways/Http/Controllers -name "*Controller.php" -exec php -l {} \;
   ```

2. Check Laravel logs:
   ```bash
   tail -n 50 /var/www/6ammart-laravel/storage/logs/laravel.log
   ```

### API Still Returns HTML

If API endpoints still return HTML from Next.js:

1. Check Apache error logs:
   ```bash
   tail -n 50 /var/log/apache2/6ammart_error.log
   ```

2. Verify Apache config:
   ```bash
   sudo apache2ctl -S
   ```

3. Check if ProxyPass is working:
   ```bash
   curl -I http://127.0.0.1/api/v1/auth/login
   ```

### React App Not Accessible

If React app is not accessible:

1. Check PM2 status:
   ```bash
   pm2 list
   pm2 logs 6ammart-react
   ```

2. Restart React app if needed:
   ```bash
   cd /var/www/6ammart-react
   pm2 restart 6ammart-react
   ```

## Files Modified

### Local Files (Fixed):
- `Modules/Gateways/Http/Controllers/SenangPayController.php`
- `Modules/Gateways/Http/Controllers/PhonepeController.php`
- `Modules/Gateways/Http/Controllers/FlutterwaveV3Controller.php`
- `Modules/Gateways/Http/Controllers/WorldPayController.php`

### Server Files (To be updated):
- `/etc/apache2/sites-available/6ammart.conf`
- `/var/www/6ammart-laravel/Modules/Gateways/Http/Controllers/*` (after sync)

## Scripts Created

1. `update-apache-config.sh` - Updates Apache configuration only
2. `deploy-fixes-to-server.sh` - Complete deployment script

Both scripts can be run on the server with sudo privileges.

