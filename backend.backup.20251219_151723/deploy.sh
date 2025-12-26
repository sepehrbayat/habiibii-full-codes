#!/bin/bash

# 🚀 Script استقرار خودکار پروژه 6amMart Laravel
# استفاده: bash deploy.sh

set -e  # توقف در صورت خطا

echo "🚀 شروع فرآیند استقرار 6amMart Laravel..."

# رنگ‌ها برای خروجی
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# متغیرهای محیطی
PROJECT_DIR="/var/www/6ammart-laravel"
PHP_VERSION="8.2"

# بررسی دسترسی root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ این اسکریپت باید با دسترسی root اجرا شود${NC}"
    exit 1
fi

echo -e "${GREEN}✅ بررسی پیش‌نیازها...${NC}"

# بررسی PHP
if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP نصب نشده است${NC}"
    exit 1
fi

PHP_VER=$(php -r 'echo PHP_VERSION;' | cut -d. -f1,2)
echo -e "${GREEN}✅ PHP نسخه $PHP_VER یافت شد${NC}"

# بررسی Composer
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}⚠️ Composer یافت نشد. در حال نصب...${NC}"
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi
echo -e "${GREEN}✅ Composer آماده است${NC}"

# بررسی Node.js
if ! command -v node &> /dev/null; then
    echo -e "${YELLOW}⚠️ Node.js یافت نشد. در حال نصب...${NC}"
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt install -y nodejs
fi
echo -e "${GREEN}✅ Node.js آماده است${NC}"

# بررسی وجود پروژه
if [ ! -d "$PROJECT_DIR" ]; then
    echo -e "${RED}❌ پوشه پروژه یافت نشد: $PROJECT_DIR${NC}"
    echo -e "${YELLOW}لطفاً ابتدا پروژه را در $PROJECT_DIR آپلود کنید${NC}"
    exit 1
fi

cd "$PROJECT_DIR"

echo -e "${GREEN}✅ تنظیم مجوزها...${NC}"
chown -R www-data:www-data "$PROJECT_DIR"
chmod -R 755 "$PROJECT_DIR"
chmod -R 775 storage bootstrap/cache

echo -e "${GREEN}✅ نصب وابستگی‌های Composer...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

echo -e "${GREEN}✅ نصب وابستگی‌های NPM...${NC}"
npm install --production

echo -e "${GREEN}✅ Build Assets...${NC}"
npm run production

echo -e "${GREEN}✅ بررسی فایل .env...${NC}"
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo -e "${YELLOW}⚠️ فایل .env از .env.example ایجاد شد. لطفاً تنظیمات را بررسی کنید${NC}"
    else
        echo -e "${RED}❌ فایل .env.example یافت نشد${NC}"
        exit 1
    fi
fi

echo -e "${GREEN}✅ تولید APP_KEY...${NC}"
php artisan key:generate --force

echo -e "${GREEN}✅ اجرای Migrations...${NC}"
php artisan migrate --force

echo -e "${GREEN}✅ ایجاد Symbolic Link برای Storage...${NC}"
php artisan storage:link

echo -e "${GREEN}✅ Publish Module Assets...${NC}"
php artisan module:publish BeautyBooking || echo -e "${YELLOW}⚠️ Module BeautyBooking یافت نشد (اختیاری)${NC}"

echo -e "${GREEN}✅ Cache Configuration...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo -e "${GREEN}✅ Optimize Autoloader...${NC}"
composer dump-autoload --optimize

echo -e "${GREEN}✅ تنظیم مجوزهای نهایی...${NC}"
chmod -R 775 storage bootstrap/cache
chmod 600 .env

echo -e "${GREEN}✅ استقرار با موفقیت انجام شد!${NC}"
echo -e "${YELLOW}⚠️ لطفاً تنظیمات .env را بررسی کنید${NC}"
echo -e "${YELLOW}⚠️ لطفاً Virtual Host Apache را تنظیم کنید${NC}"

