# 📁 دستورالعمل انتقال پروژه‌ها به /var/www/

## هدف
انتقال پروژه‌های Laravel و React به مسیر `/var/www/` برای تطبیق دقیق با ساختار سرور

## مسیرهای جدید
- **Laravel:** `/var/www/6ammart-laravel/`
- **React:** `/var/www/6ammart-react/`

## روش اجرا

### گزینه 1: استفاده از اسکریپت (پیشنهادی)
```bash
sudo bash /home/sepehr/Projects/6ammart-laravel/move_to_var_www.sh
```

### گزینه 2: دستی
```bash
# ایجاد دایرکتوری
sudo mkdir -p /var/www

# کپی Laravel
sudo cp -r /home/sepehr/Projects/6ammart-laravel /var/www/6ammart-laravel

# کپی React
sudo cp -r /home/sepehr/Projects/6ammart-react /var/www/6ammart-react

# تنظیم دسترسی‌ها
sudo chown -R www-data:www-data /var/www/6ammart-laravel
sudo chown -R www-data:www-data /var/www/6ammart-react
sudo chmod -R 755 /var/www/6ammart-laravel
sudo chmod -R 755 /var/www/6ammart-react
```

## نکات مهم

1. **بکاپ خودکار:** اگر پروژه‌هایی در `/var/www/` وجود داشته باشند، به صورت خودکار بکاپ گرفته می‌شوند

2. **پروژه‌های اصلی:** پروژه‌های اصلی در `/home/sepehr/Projects/` باقی می‌مانند (می‌توانید بعداً حذف کنید)

3. **دسترسی:** برای کار با `/var/www/` ممکن است نیاز به `sudo` داشته باشید

4. **بعد از انتقال:** 
   - مسیرهای پروژه را در IDE/Editor خود به‌روزرسانی کنید
   - اگر از Git استفاده می‌کنید، repository را در مسیر جدید تنظیم کنید

## بررسی بعد از انتقال

```bash
# بررسی وجود پروژه‌ها
ls -la /var/www/6ammart-laravel
ls -la /var/www/6ammart-react

# بررسی دسترسی‌ها
ls -la /var/www/
```

## بازگشت به حالت قبلی

اگر می‌خواهید به حالت قبلی برگردید:
```bash
sudo rm -rf /var/www/6ammart-laravel
sudo rm -rf /var/www/6ammart-react
```

---
**تاریخ ایجاد:** ۱۷ دسامبر ۲۰۲۵
