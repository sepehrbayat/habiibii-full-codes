#!/bin/bash

# 🔧 Script تنظیمات اولیه سرور برای 6amMart Laravel
# استفاده: bash setup-server.sh

set -e

echo "🔧 شروع تنظیمات سرور..."

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# بررسی دسترسی root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ این اسکریپت باید با دسترسی root اجرا شود${NC}"
    exit 1
fi

echo -e "${GREEN}✅ به‌روزرسانی سیستم...${NC}"
apt update && apt upgrade -y

echo -e "${GREEN}✅ نصب PHP و Extensions...${NC}"
apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml \
    php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath \
    php8.2-intl php8.2-soap php8.2-redis php8.2-imagick

echo -e "${GREEN}✅ نصب Composer...${NC}"
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

echo -e "${GREEN}✅ نصب Node.js و NPM...${NC}"
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt install -y nodejs
fi

echo -e "${GREEN}✅ نصب MySQL...${NC}"
if ! command -v mysql &> /dev/null; then
    apt install -y mysql-server
    systemctl start mysql
    systemctl enable mysql
fi

echo -e "${GREEN}✅ نصب و تنظیم Apache...${NC}"
apt install -y apache2
a2enmod rewrite
a2enmod headers
a2enmod ssl
systemctl restart apache2
systemctl enable apache2

echo -e "${GREEN}✅ نصب PM2 (برای React App)...${NC}"
if ! command -v pm2 &> /dev/null; then
    npm install -g pm2
fi

echo -e "${GREEN}✅ تنظیم Firewall...${NC}"
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

echo -e "${GREEN}✅ تنظیمات سرور با موفقیت انجام شد!${NC}"

