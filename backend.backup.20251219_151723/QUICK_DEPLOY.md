# ⚡ راهنمای سریع استقرار

## 🚀 دستورات سریع

### 1. تنظیمات اولیه سرور
```bash
bash setup-server.sh
```

### 2. آپلود پروژه
```bash
# در سیستم محلی
tar -czf 6ammart-laravel.tar.gz --exclude='node_modules' --exclude='vendor' \
    --exclude='.git' --exclude='storage/logs/*' --exclude='.env' .

scp 6ammart-laravel.tar.gz root@188.245.192.118:/var/www/

# در سرور
cd /var/www
tar -xzf 6ammart-laravel.tar.gz
cd 6ammart-laravel
```

### 3. استقرار خودکار
```bash
cd /var/www/6ammart-laravel
bash deploy.sh
```

### 4. تنظیم دیتابیس
```bash
mysql -u root -p
```

```sql
CREATE DATABASE 6ammart_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER '6ammart_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON 6ammart_db.* TO '6ammart_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. تنظیم .env
```bash
nano .env
```

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://188.245.192.118

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=6ammart_db
DB_USERNAME=6ammart_user
DB_PASSWORD=your_password
```

### 6. تنظیم Apache
```bash
nano /etc/apache2/sites-available/6ammart.conf
```

```apache
<VirtualHost *:80>
    ServerName 188.245.192.118
    DocumentRoot /var/www/6ammart-laravel/public

    <Directory /var/www/6ammart-laravel/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

```bash
a2ensite 6ammart.conf
a2dissite 000-default.conf
systemctl reload apache2
```

---

## ⚛️ React Frontend

### 1. تنظیم CORS در Laravel
```bash
nano config/cors.php
# تنظیم allowed_origins
php artisan config:clear
```

### 2. Build و Deploy React
```bash
# در سیستم محلی
cd /path/to/react-app
npm run build
tar -czf react-build.tar.gz build/
scp react-build.tar.gz root@188.245.192.118:/var/www/

# در سرور
cd /var/www
tar -xzf react-build.tar.gz
mv build react-app
cd react-app
npm install -g serve pm2
pm2 start ecosystem.config.js
pm2 save
```

---

## ✅ بررسی

```bash
# تست Laravel
curl http://188.245.192.118/api/v1/configurations

# تست React
curl http://188.245.192.118:3000

# بررسی Logs
tail -f storage/logs/laravel.log
pm2 logs 6ammart-react
```

---

## 🔧 دستورات مفید

```bash
# Clear Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Update
git pull
composer install --no-dev
php artisan migrate --force
php artisan config:cache

# PM2
pm2 status
pm2 logs
pm2 restart 6ammart-react
```

---

**برای جزئیات بیشتر**: `DEPLOYMENT_GUIDE.md`

