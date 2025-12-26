#!/bin/bash

# OpenTelemetry Setup Script for Beauty Booking Module
# اسکریپت راه‌اندازی OpenTelemetry برای ماژول Beauty Booking

echo "=========================================="
echo "OpenTelemetry Setup for Beauty Booking"
echo "راه‌اندازی OpenTelemetry برای Beauty Booking"
echo "=========================================="
echo ""

# Check if Observe Agent is running
echo "Checking Observe Agent status..."
echo "بررسی وضعیت Observe Agent..."
if systemctl is-active --quiet observe-agent; then
    echo "✓ Observe Agent is running"
    echo "✓ Observe Agent در حال اجرا است"
else
    echo "✗ Observe Agent is not running. Please start it first:"
    echo "✗ Observe Agent در حال اجرا نیست. لطفاً ابتدا آن را راه‌اندازی کنید:"
    echo "  sudo systemctl start observe-agent"
    exit 1
fi

# Check if .env file exists
if [ ! -f .env ]; then
    echo "✗ .env file not found. Please create it first."
    echo "✗ فایل .env پیدا نشد. لطفاً ابتدا آن را ایجاد کنید."
    exit 1
fi

# Add OpenTelemetry environment variables if not present
echo ""
echo "Adding OpenTelemetry configuration to .env..."
echo "افزودن تنظیمات OpenTelemetry به .env..."

# Check if OTEL_ENABLED exists
if ! grep -q "OTEL_ENABLED" .env; then
    echo "" >> .env
    echo "# OpenTelemetry Configuration" >> .env
    echo "# تنظیمات OpenTelemetry" >> .env
    echo "OTEL_ENABLED=true" >> .env
    echo "OTEL_EXPORTER_OTLP_ENDPOINT=http://localhost:4317" >> .env
    echo "OTEL_EXPORTER_OTLP_PROTOCOL=grpc" >> .env
    echo "OTEL_SERVICE_NAME=hooshex" >> .env
    echo "OTEL_ENVIRONMENT=test1" >> .env
    echo "OTEL_TEAM=test2" >> .env
    echo "OTEL_BEAUTY_BOOKING_ENABLED=true" >> .env
    echo "OTEL_SAMPLING_RATE=1.0" >> .env
    echo "✓ Added OpenTelemetry configuration to .env"
    echo "✓ تنظیمات OpenTelemetry به .env اضافه شد"
else
    echo "✓ OpenTelemetry configuration already exists in .env"
    echo "✓ تنظیمات OpenTelemetry از قبل در .env وجود دارد"
fi

# Install Composer dependencies
echo ""
echo "Installing OpenTelemetry packages..."
echo "نصب پکیج‌های OpenTelemetry..."
composer require open-telemetry/sdk open-telemetry/exporter-otlp open-telemetry/sem-conv --no-interaction

if [ $? -eq 0 ]; then
    echo "✓ OpenTelemetry packages installed successfully"
    echo "✓ پکیج‌های OpenTelemetry با موفقیت نصب شدند"
else
    echo "✗ Failed to install OpenTelemetry packages"
    echo "✗ نصب پکیج‌های OpenTelemetry ناموفق بود"
    exit 1
fi

# Clear Laravel cache
echo ""
echo "Clearing Laravel cache..."
echo "پاک کردن کش Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "=========================================="
echo "Setup Complete!"
echo "راه‌اندازی کامل شد!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "مراحل بعدی:"
echo "1. Verify Observe Agent is running: sudo systemctl status observe-agent"
echo "2. Check agent statistics: observe-agent status"
echo "3. Test your application and check traces in Observe dashboard"
echo ""
echo "1. بررسی اجرای Observe Agent: sudo systemctl status observe-agent"
echo "2. بررسی آمار agent: observe-agent status"
echo "3. تست برنامه خود و بررسی traceها در داشبورد Observe"
echo ""

