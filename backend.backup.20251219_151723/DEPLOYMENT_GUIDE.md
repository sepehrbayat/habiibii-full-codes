# 🚀 راهنمای کامل استقرار پروژه 6amMart Laravel

## 📋 فهرست مطالب
1. [پیش‌نیازها](#پیش‌نیازها)
2. [تنظیمات سرور](#تنظیمات-سرور)
3. [آپلود و نصب پروژه](#آپلود-و-نصب-پروژه)
4. [تنظیمات دیتابیس](#تنظیمات-دیتابیس)
5. [تنظیمات محیطی](#تنظیمات-محیطی)
6. [بهینه‌سازی برای Production](#بهینه-سازی-برای-production)
7. [اتصال React Frontend](#اتصال-react-frontend)
8. [بررسی نهایی](#بررسی-نهایی)

---

## 🔧 پیش‌نیازها

### الزامات سرور
- **PHP**: >= 8.2
- **Composer**: آخرین نسخه
- **MySQL**: >= 5.7 یا MariaDB >= 10.3
- **Node.js**: >= 16.x (برای build assets)
- **NPM**: آخرین نسخه
- **Apache/Nginx**: با mod_rewrite فعال
- **Extensions PHP مورد نیاز**:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
  - cURL
  - GD
  - Zip
  - MySQLi

### بررسی پیش‌نیازها
```bash
php -v
composer --version
mysql --version
node -v
npm -v
```

---

## 🖥️ تنظیمات سرور

### اطلاعات سرور
- **IP**: 188.245.192.118
- **User**: root
- **Password**: 6amMart

### اتصال به سرور
```bash
ssh root@188.245.192.118
# یا
ssh -p 22 root@188.245.192.118
```

### نصب پیش‌نیازها (اگر نصب نشده‌اند)

#### Ubuntu/Debian
```bash
# به‌روزرسانی سیستم
apt update && apt upgrade -y

# نصب PHP و Extensions
apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml \
    php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath \
    php8.2-intl php8.2-soap php8.2-redis

# نصب Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# نصب Node.js و NPM
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs

# نصب MySQL
apt install -y mysql-server

# نصب Apache
apt install -y apache2
a2enmod rewrite
a2enmod headers
a2enmod ssl
```

---

## 📤 آپلود و نصب پروژه

### روش 1: استفاده از Git (توصیه می‌شود)
```bash
# در سرور
cd /var/www
git clone <repository-url> 6ammart-laravel
cd 6ammart-laravel
```

### روش 2: آپلود مستقیم
```bash
# در سیستم محلی
tar -czf 6ammart-laravel.tar.gz --exclude='node_modules' --exclude='vendor' \
    --exclude='.git' --exclude='storage/logs/*' --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' --exclude='storage/framework/views/*' \
    --exclude='.env' .

# آپلود به سرور
scp 6ammart-laravel.tar.gz root@188.245.192.118:/var/www/

# در سرور
cd /var/www
tar -xzf 6ammart-laravel.tar.gz
cd 6ammart-laravel
```

### تنظیم مجوزها
```bash
# تنظیم مالکیت
chown -R www-data:www-data /var/www/6ammart-laravel

# تنظیم مجوزهای پوشه‌ها
chmod -R 755 /var/www/6ammart-laravel
chmod -R 775 /var/www/6ammart-laravel/storage
chmod -R 775 /var/www/6ammart-laravel/bootstrap/cache

# ایجاد symbolic link برای storage
php artisan storage:link
```

---

## 🗄️ تنظیمات دیتابیس

### ایجاد دیتابیس
```bash
mysql -u root -p
```

```sql
CREATE DATABASE 6ammart_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER '6ammart_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON 6ammart_db.* TO '6ammart_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### ایمپورت دیتابیس (اگر فایل SQL دارید)
```bash
mysql -u 6ammart_user -p 6ammart_db < habiibii.sql
```

---

## ⚙️ تنظیمات محیطی

### ایجاد فایل .env
```bash
cd /var/www/6ammart-laravel
cp .env.example .env
nano .env
```

### تنظیمات .env برای Production
```env
APP_NAME="6amMart"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://188.245.192.118
APP_INSTALL=true
APP_MODE=live
APP_LOG_LEVEL=error

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=6ammart_db
DB_USERNAME=6ammart_user
DB_PASSWORD=your_secure_password

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync

# Redis (اختیاری)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="${APP_NAME}"

# Firebase (برای Push Notifications)
FIREBASE_PROJECT=app
FIREBASE_CREDENTIALS=/var/www/6ammart-laravel/storage/app/firebase-credentials.json

# Payment Gateways (تنظیم کنید)
STRIPE_KEY=
STRIPE_SECRET=
PAYPAL_CLIENT_ID=
PAYPAL_CLIENT_SECRET=
RAZORPAY_KEY=
RAZORPAY_SECRET=

# Purchase Code (اگر نیاز دارید)
PURCHASE_CODE=
BUYER_USERNAME=
SOFTWARE_ID=MzY3NzIxMTI=
SOFTWARE_VERSION=3.3
```

### تولید APP_KEY
```bash
php artisan key:generate
```

---

## 🏗️ نصب وابستگی‌ها

### نصب Composer Dependencies
```bash
cd /var/www/6ammart-laravel
composer install --no-dev --optimize-autoloader
```

### نصب NPM Dependencies و Build Assets
```bash
npm install
npm run production
```

### اجرای Migrations
```bash
php artisan migrate --force
```

### Publish Module Assets
```bash
php artisan module:publish BeautyBooking
```

---

## 🚀 بهینه‌سازی برای Production

### Cache Configuration
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Optimize Autoloader
```bash
composer dump-autoload --optimize
```

### تنظیمات Apache

#### ایجاد Virtual Host
```bash
nano /etc/apache2/sites-available/6ammart.conf
```

```apache
<VirtualHost *:80>
    ServerName 188.245.192.118
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/6ammart-laravel/public

    <Directory /var/www/6ammart-laravel/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/6ammart_error.log
    CustomLog ${APACHE_LOG_DIR}/6ammart_access.log combined
</VirtualHost>
```

#### فعال‌سازی Virtual Host
```bash
a2ensite 6ammart.conf
a2dissite 000-default.conf
systemctl reload apache2
```

### تنظیمات Nginx (اگر از Nginx استفاده می‌کنید)
```nginx
server {
    listen 80;
    server_name 188.245.192.118;
    root /var/www/6ammart-laravel/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## ⚛️ اتصال React Frontend

### تنظیمات CORS در Laravel

#### ویرایش config/cors.php
```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',  // Development
        'http://188.245.192.118:3000',  // Production React App
        'https://your-react-domain.com',  // Production Domain
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,  // برای استفاده از cookies
];
```

### تنظیمات .env برای API
```env
# در فایل .env
APP_URL=http://188.245.192.118
FRONTEND_URL=http://188.245.192.118:3000
# یا
FRONTEND_URL=https://your-react-domain.com
```

### تنظیمات React Frontend

#### ایجاد فایل .env در پروژه React
```env
REACT_APP_API_URL=http://188.245.192.118/api/v1
REACT_APP_API_BASE_URL=http://188.245.192.118
REACT_APP_WS_URL=ws://188.245.192.118:6001
```

#### مثال استفاده در React
```javascript
// config/api.js
const API_BASE_URL = process.env.REACT_APP_API_BASE_URL || 'http://188.245.192.118';
const API_URL = `${API_BASE_URL}/api/v1`;

// استفاده در Axios
import axios from 'axios';

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,  // برای cookies
});

// اضافه کردن token به header
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
```

### استقرار React Frontend

#### Build React App
```bash
cd /path/to/react-app
npm install
npm run build
```

#### آپلود Build به سرور
```bash
# در سیستم محلی
cd /path/to/react-app
npm run build
tar -czf react-build.tar.gz build/

# آپلود به سرور
scp react-build.tar.gz root@188.245.192.118:/var/www/

# در سرور
cd /var/www
tar -xzf react-build.tar.gz
mv build react-app
```

#### تنظیم Apache برای React App
```apache
<VirtualHost *:3000>
    ServerName 188.245.192.118
    DocumentRoot /var/www/react-app

    <Directory /var/www/react-app>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        
        # برای React Router
        RewriteEngine On
        RewriteBase /
        RewriteRule ^index\.html$ - [L]
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . /index.html [L]
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/react_error.log
    CustomLog ${APACHE_LOG_DIR}/react_access.log combined
</VirtualHost>
```

#### یا استفاده از PM2 برای React (توصیه می‌شود)
```bash
# نصب PM2
npm install -g pm2

# ایجاد فایل ecosystem.config.js در پروژه React
module.exports = {
  apps: [{
    name: '6ammart-react',
    script: 'serve',
    args: '-s build -l 3000',
    env: {
      NODE_ENV: 'production',
      PORT: 3000
    }
  }]
};

# اجرا با PM2
pm2 start ecosystem.config.js
pm2 save
pm2 startup
```

---

## ✅ بررسی نهایی

### بررسی دسترسی‌ها
```bash
# بررسی مجوزها
ls -la /var/www/6ammart-laravel/storage
ls -la /var/www/6ammart-laravel/bootstrap/cache

# بررسی symbolic link
ls -la /var/www/6ammart-laravel/public/storage
```

### تست API
```bash
# تست اتصال
curl http://188.245.192.118/api/v1/configurations

# تست Health Check
curl http://188.245.192.118/api/v1/health
```

### بررسی Logs
```bash
# Laravel Logs
tail -f /var/www/6ammart-laravel/storage/logs/laravel.log

# Apache Logs
tail -f /var/log/apache2/6ammart_error.log
tail -f /var/log/apache2/6ammart_access.log
```

### بررسی Performance
```bash
# بررسی PHP-FPM
systemctl status php8.2-fpm

# بررسی Apache
systemctl status apache2

# بررسی MySQL
systemctl status mysql
```

---

## 🔒 امنیت

### تنظیمات امنیتی مهم

#### 1. تنظیم مجوزهای فایل .env
```bash
chmod 600 /var/www/6ammart-laravel/.env
```

#### 2. غیرفعال کردن Directory Listing
```apache
# در .htaccess یا Virtual Host
Options -Indexes
```

#### 3. تنظیم Firewall
```bash
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

#### 4. تنظیم SSL (HTTPS)
```bash
# نصب Certbot
apt install certbot python3-certbot-apache

# دریافت گواهینامه
certbot --apache -d yourdomain.com
```

---

## 📝 دستورات مفید

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Update Project
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Monitor Logs
```bash
# Real-time log monitoring
tail -f storage/logs/laravel.log

# Check specific errors
grep "ERROR" storage/logs/laravel.log
```

---

## 🆘 عیب‌یابی

### مشکلات رایج

#### 1. خطای 500 Internal Server Error
```bash
# بررسی مجوزها
chmod -R 775 storage bootstrap/cache

# بررسی .env
php artisan config:clear

# بررسی logs
tail -f storage/logs/laravel.log
```

#### 2. خطای CORS
- بررسی `config/cors.php`
- بررسی `allowed_origins`
- بررسی middleware در `app/Http/Kernel.php`

#### 3. خطای Database Connection
```bash
# تست اتصال
php artisan tinker
>>> DB::connection()->getPdo();
```

#### 4. خطای Storage
```bash
php artisan storage:link
chmod -R 775 storage
```

---

## 📞 پشتیبانی

در صورت بروز مشکل:
1. بررسی Logs
2. بررسی مجوزها
3. بررسی تنظیمات .env
4. بررسی اتصال دیتابیس
5. بررسی CORS settings

---

**آخرین به‌روزرسانی**: 2024-12-XX
**نسخه**: 1.0

