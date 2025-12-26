# 📚 مستندات استقرار 6amMart Laravel

## 📁 فایل‌های موجود

### راهنماهای اصلی
- **`DEPLOYMENT_GUIDE.md`** - راهنمای کامل و جامع استقرار (پیشنهاد می‌شود ابتدا این را بخوانید)
- **`QUICK_DEPLOY.md`** - راهنمای سریع برای استقرار فوری
- **`REACT_DEPLOYMENT_GUIDE.md`** - راهنمای کامل استقرار React Frontend
- **`DEPLOYMENT_CHECKLIST.md`** - چک‌لیست کامل برای اطمینان از انجام تمام مراحل

### اسکریپت‌های خودکار
- **`setup-server.sh`** - نصب و تنظیم پیش‌نیازهای سرور
- **`deploy.sh`** - استقرار خودکار پروژه Laravel

### فایل‌های پیکربندی
- **`apache-vhost.conf`** - تنظیمات Virtual Host برای Apache
- **`nginx-vhost.conf`** - تنظیمات Virtual Host برای Nginx
- **`react-ecosystem.config.js`** - تنظیمات PM2 برای React App

---

## 🚀 شروع سریع

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
```

### 3. استقرار در سرور
```bash
# در سرور
cd /var/www
tar -xzf 6ammart-laravel.tar.gz
cd 6ammart-laravel
bash deploy.sh
```

### 4. تنظیم دیتابیس و .env
```bash
# ایجاد دیتابیس
mysql -u root -p

# تنظیم .env
nano .env
php artisan key:generate
```

### 5. تنظیم Web Server
```bash
# Apache
cp apache-vhost.conf /etc/apache2/sites-available/6ammart.conf
a2ensite 6ammart.conf
systemctl reload apache2

# یا Nginx
cp nginx-vhost.conf /etc/nginx/sites-available/6ammart
ln -s /etc/nginx/sites-available/6ammart /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

---

## ⚛️ استقرار React Frontend

### 1. Build پروژه
```bash
cd /path/to/react-app
npm run build
```

### 2. آپلود به سرور
```bash
scp -r build root@188.245.192.118:/var/www/react-app
```

### 3. اجرا با PM2
```bash
cd /var/www/react-app
cp react-ecosystem.config.js ecosystem.config.js
npm install -g serve pm2
pm2 start ecosystem.config.js
pm2 save
pm2 startup
```

---

## 📖 مطالعه بیشتر

برای جزئیات کامل، فایل‌های زیر را مطالعه کنید:

1. **`DEPLOYMENT_GUIDE.md`** - راهنمای کامل با تمام جزئیات
2. **`REACT_DEPLOYMENT_GUIDE.md`** - راهنمای اتصال React به Laravel
3. **`DEPLOYMENT_CHECKLIST.md`** - چک‌لیست کامل

---

## 🔧 تنظیمات مهم

### اطلاعات سرور
- **IP**: 188.245.192.118
- **User**: root
- **Password**: 6amMart

### مسیرهای مهم
- **Laravel**: `/var/www/6ammart-laravel`
- **React**: `/var/www/react-app`
- **Logs**: `/var/www/6ammart-laravel/storage/logs`

### پورت‌ها
- **Laravel API**: 80 (HTTP) یا 443 (HTTPS)
- **React App**: 3000
- **WebSocket**: 6001

---

## ✅ بررسی نهایی

```bash
# تست Laravel
curl http://188.245.192.118/api/v1/configurations

# تست React
curl http://188.245.192.118:3000

# بررسی Logs
tail -f /var/www/6ammart-laravel/storage/logs/laravel.log
pm2 logs 6ammart-react
```

---

## 🆘 پشتیبانی

در صورت بروز مشکل:
1. بررسی `DEPLOYMENT_GUIDE.md` بخش عیب‌یابی
2. بررسی Logs
3. بررسی تنظیمات .env
4. بررسی مجوزها

---

**آخرین به‌روزرسانی**: 2024-12-XX

