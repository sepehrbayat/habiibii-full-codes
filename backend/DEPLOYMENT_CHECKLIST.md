# ✅ چک‌لیست استقرار 6amMart Laravel

## 📋 قبل از استقرار

- [ ] بررسی پیش‌نیازهای سرور (PHP 8.2+, MySQL, Apache/Nginx)
- [ ] اتصال به سرور با SSH
- [ ] تهیه Backup از دیتابیس (اگر وجود دارد)
- [ ] تهیه Backup از فایل‌های پروژه (اگر وجود دارد)

## 🖥️ تنظیمات سرور

- [ ] اجرای `setup-server.sh` برای نصب پیش‌نیازها
- [ ] بررسی نصب PHP و Extensions
- [ ] بررسی نصب Composer
- [ ] بررسی نصب Node.js و NPM
- [ ] بررسی نصب MySQL
- [ ] بررسی نصب Apache/Nginx

## 📤 آپلود پروژه

- [ ] آپلود فایل‌های پروژه به `/var/www/6ammart-laravel`
- [ ] تنظیم مجوزهای پوشه‌ها
- [ ] ایجاد symbolic link برای storage

## 🗄️ تنظیمات دیتابیس

- [ ] ایجاد دیتابیس جدید
- [ ] ایجاد کاربر دیتابیس
- [ ] اعطای دسترسی‌های لازم
- [ ] ایمپورت دیتابیس (اگر فایل SQL دارید)
- [ ] تست اتصال دیتابیس

## ⚙️ تنظیمات محیطی

- [ ] ایجاد فایل `.env` از `.env.example`
- [ ] تنظیم `APP_ENV=production`
- [ ] تنظیم `APP_DEBUG=false`
- [ ] تنظیم `APP_URL`
- [ ] تنظیم اطلاعات دیتابیس
- [ ] تولید `APP_KEY`
- [ ] تنظیم اطلاعات Mail
- [ ] تنظیم اطلاعات Payment Gateways
- [ ] تنظیم اطلاعات Firebase (برای Push Notifications)

## 📦 نصب وابستگی‌ها

- [ ] نصب Composer Dependencies (`composer install --no-dev`)
- [ ] نصب NPM Dependencies (`npm install`)
- [ ] Build Assets (`npm run production`)

## 🗃️ دیتابیس

- [ ] اجرای Migrations (`php artisan migrate --force`)
- [ ] Publish Module Assets (`php artisan module:publish BeautyBooking`)

## 🚀 بهینه‌سازی

- [ ] Cache Configuration (`php artisan config:cache`)
- [ ] Cache Routes (`php artisan route:cache`)
- [ ] Cache Views (`php artisan view:cache`)
- [ ] Cache Events (`php artisan event:cache`)
- [ ] Optimize Autoloader (`composer dump-autoload --optimize`)

## 🌐 تنظیمات Web Server

- [ ] ایجاد Virtual Host برای Apache/Nginx
- [ ] تنظیم DocumentRoot به `/var/www/6ammart-laravel/public`
- [ ] فعال‌سازی mod_rewrite
- [ ] فعال‌سازی Virtual Host
- [ ] Restart Web Server

## ⚛️ تنظیمات React Frontend

- [ ] تنظیم CORS در `config/cors.php`
- [ ] Clear Cache Laravel
- [ ] Build React App (`npm run build`)
- [ ] آپلود Build به سرور
- [ ] تنظیم PM2 یا Apache/Nginx برای React
- [ ] تست اتصال React به Laravel API

## 🔒 امنیت

- [ ] تنظیم مجوز فایل `.env` (600)
- [ ] غیرفعال کردن Directory Listing
- [ ] تنظیم Firewall
- [ ] تنظیم SSL/HTTPS (اختیاری اما توصیه می‌شود)

## ✅ تست نهایی

- [ ] تست دسترسی به صفحه اصلی
- [ ] تست API Endpoints
- [ ] تست اتصال React به Laravel
- [ ] بررسی Logs برای خطا
- [ ] تست Login/Register
- [ ] تست ماژول Beauty Booking (اگر فعال است)

## 📝 مستندات

- [ ] ثبت اطلاعات دیتابیس
- [ ] ثبت اطلاعات API Keys
- [ ] ثبت URL های مهم
- [ ] ثبت دستورات مفید

---

## 🆘 در صورت مشکل

1. بررسی Logs: `tail -f storage/logs/laravel.log`
2. بررسی مجوزها: `ls -la storage bootstrap/cache`
3. Clear Cache: `php artisan config:clear`
4. بررسی .env: `cat .env`
5. بررسی اتصال دیتابیس: `php artisan tinker`

---

**تاریخ استقرار**: _______________
**توسط**: _______________
**نسخه**: _______________

