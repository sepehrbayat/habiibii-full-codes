#!/bin/bash

# 🚀 Script آپلود خودکار پروژه‌های Laravel و React به سرور
# استفاده: bash upload-to-server.sh

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# اطلاعات سرور
SERVER_IP="188.245.192.118"
SERVER_USER="root"
SERVER_PASS="6amMart"
SERVER_LARAVEL_PATH="/var/www/6ammart-laravel"
SERVER_REACT_PATH="/var/www/6ammart-react"

# مسیرهای محلی
LOCAL_LARAVEL="/home/sepehr/Projects/6ammart-laravel"
LOCAL_REACT="/home/sepehr/Projects/6ammart-react"

echo -e "${GREEN}🚀 شروع فرآیند آپلود...${NC}"

# بررسی نصب sshpass
if ! command -v sshpass &> /dev/null; then
    echo -e "${YELLOW}⚠️ sshpass نصب نشده است. در حال نصب...${NC}"
    sudo apt-get update
    sudo apt-get install -y sshpass
fi

# تابع برای اجرای دستور در سرور
run_remote() {
    sshpass -p "$SERVER_PASS" ssh -o StrictHostKeyChecking=no "$SERVER_USER@$SERVER_IP" "$1"
}

# تابع برای آپلود فایل
upload_file() {
    sshpass -p "$SERVER_PASS" scp -o StrictHostKeyChecking=no "$1" "$SERVER_USER@$SERVER_IP:$2"
}

# تابع برای آپلود دایرکتوری
upload_dir() {
    sshpass -p "$SERVER_PASS" rsync -avz --progress -e "ssh -o StrictHostKeyChecking=no" \
        --exclude='node_modules' \
        --exclude='vendor' \
        --exclude='.git' \
        --exclude='.next' \
        --exclude='storage/logs/*' \
        --exclude='storage/framework/cache/*' \
        --exclude='storage/framework/sessions/*' \
        --exclude='storage/framework/views/*' \
        --exclude='.env' \
        --exclude='*.log' \
        --exclude='tmp/' \
        "$1/" "$SERVER_USER@$SERVER_IP:$2/"
}

echo -e "${GREEN}✅ ایجاد پوشه‌های لازم در سرور...${NC}"
run_remote "mkdir -p $SERVER_LARAVEL_PATH"
run_remote "mkdir -p $SERVER_REACT_PATH"

echo -e "${GREEN}✅ آپلود پروژه Laravel...${NC}"
upload_dir "$LOCAL_LARAVEL" "$SERVER_LARAVEL_PATH"

echo -e "${GREEN}✅ آپلود پروژه React...${NC}"
upload_dir "$LOCAL_REACT" "$SERVER_REACT_PATH"

echo -e "${GREEN}✅ تنظیم مجوزها در سرور...${NC}"
run_remote "chown -R www-data:www-data $SERVER_LARAVEL_PATH"
run_remote "chmod -R 755 $SERVER_LARAVEL_PATH"
run_remote "chmod -R 775 $SERVER_LARAVEL_PATH/storage"
run_remote "chmod -R 775 $SERVER_LARAVEL_PATH/bootstrap/cache"

echo -e "${GREEN}✅ آپلود فایل‌های پیکربندی...${NC}"
upload_file "$LOCAL_LARAVEL/deploy.sh" "$SERVER_LARAVEL_PATH/"
upload_file "$LOCAL_LARAVEL/setup-server.sh" "$SERVER_LARAVEL_PATH/"
upload_file "$LOCAL_LARAVEL/apache-vhost.conf" "$SERVER_LARAVEL_PATH/"
upload_file "$LOCAL_LARAVEL/react-ecosystem.config.js" "$SERVER_REACT_PATH/"

echo -e "${GREEN}✅ تنظیم مجوزهای اجرایی...${NC}"
run_remote "chmod +x $SERVER_LARAVEL_PATH/deploy.sh"
run_remote "chmod +x $SERVER_LARAVEL_PATH/setup-server.sh"

echo -e "${GREEN}✅ آپلود با موفقیت انجام شد!${NC}"
echo -e "${YELLOW}⚠️ لطفاً در سرور دستورات زیر را اجرا کنید:${NC}"
echo -e "${YELLOW}1. cd $SERVER_LARAVEL_PATH && bash setup-server.sh${NC}"
echo -e "${YELLOW}2. cd $SERVER_LARAVEL_PATH && bash deploy.sh${NC}"

