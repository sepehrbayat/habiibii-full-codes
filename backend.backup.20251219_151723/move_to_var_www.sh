#!/bin/bash
# اسکریپت انتقال پروژه‌ها به /var/www/ برای تطبیق با سرور

set -e

echo "🔄 انتقال پروژه‌ها به مسیر /var/www/ برای تطبیق با سرور..."
echo ""

# بررسی دسترسی root
if [ "$EUID" -ne 0 ]; then 
    echo "⚠️  این اسکریپت نیاز به دسترسی sudo دارد"
    echo "لطفاً با دستور زیر اجرا کنید:"
    echo "sudo bash $0"
    exit 1
fi

SOURCE_LARAVEL="/home/sepehr/Projects/6ammart-laravel"
SOURCE_REACT="/home/sepehr/Projects/6ammart-react"
TARGET_LARAVEL="/var/www/6ammart-laravel"
TARGET_REACT="/var/www/6ammart-react"

# ایجاد دایرکتوری /var/www اگر وجود ندارد
if [ ! -d "/var/www" ]; then
    echo "📁 ایجاد دایرکتوری /var/www..."
    mkdir -p /var/www
    chmod 755 /var/www
fi

# بررسی وجود پروژه‌ها در مسیر فعلی
if [ ! -d "$SOURCE_LARAVEL" ]; then
    echo "❌ پروژه Laravel در $SOURCE_LARAVEL پیدا نشد"
    exit 1
fi

if [ ! -d "$SOURCE_REACT" ]; then
    echo "❌ پروژه React در $SOURCE_REACT پیدا نشد"
    exit 1
fi

# اگر پروژه‌ها در /var/www/ وجود دارند، بکاپ بگیر
if [ -d "$TARGET_LARAVEL" ]; then
    echo "📦 بکاپ از Laravel موجود..."
    BACKUP_LARAVEL="/var/www/6ammart-laravel.backup.$(date +%Y%m%d_%H%M%S)"
    mv "$TARGET_LARAVEL" "$BACKUP_LARAVEL"
    echo "✅ بکاپ Laravel در: $BACKUP_LARAVEL"
fi

if [ -d "$TARGET_REACT" ]; then
    echo "📦 بکاپ از React موجود..."
    BACKUP_REACT="/var/www/6ammart-react.backup.$(date +%Y%m%d_%H%M%S)"
    mv "$TARGET_REACT" "$BACKUP_REACT"
    echo "✅ بکاپ React در: $BACKUP_REACT"
fi

# کپی کردن پروژه‌ها
echo ""
echo "📋 کپی کردن Laravel..."
cp -r "$SOURCE_LARAVEL" "$TARGET_LARAVEL"
echo "✅ Laravel کپی شد"

echo ""
echo "📋 کپی کردن React..."
cp -r "$SOURCE_REACT" "$TARGET_REACT"
echo "✅ React کپی شد"

# تنظیم دسترسی‌ها
echo ""
echo "🔐 تنظیم دسترسی‌ها..."
chown -R www-data:www-data "$TARGET_LARAVEL" 2>/dev/null || chown -R $SUDO_USER:$SUDO_USER "$TARGET_LARAVEL"
chown -R www-data:www-data "$TARGET_REACT" 2>/dev/null || chown -R $SUDO_USER:$SUDO_USER "$TARGET_REACT"

chmod -R 755 "$TARGET_LARAVEL"
chmod -R 755 "$TARGET_REACT"

echo "✅ دسترسی‌ها تنظیم شد"

# نمایش نتیجه
echo ""
echo "🎉 انتقال کامل شد!"
echo ""
echo "📁 مسیرهای جدید:"
echo "   Laravel: $TARGET_LARAVEL"
echo "   React:   $TARGET_REACT"
echo ""
echo "📝 نکته: پروژه‌های اصلی در $SOURCE_LARAVEL و $SOURCE_REACT باقی مانده‌اند"
echo "   می‌توانید بعداً آنها را حذف کنید اگر دیگر نیازی ندارید"

