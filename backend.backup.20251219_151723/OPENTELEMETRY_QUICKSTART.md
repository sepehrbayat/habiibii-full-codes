# OpenTelemetry Quick Start Guide
# راهنمای شروع سریع OpenTelemetry

## Quick Setup
## راه‌اندازی سریع

### Step 1: Run the Setup Script
### مرحله 1: اجرای اسکریپت راه‌اندازی

```bash
./setup-opentelemetry.sh
```

This script will:
این اسکریپت:

- Check if Observe Agent is running
- بررسی می‌کند که Observe Agent در حال اجرا است
- Add OpenTelemetry configuration to `.env`
- تنظیمات OpenTelemetry را به `.env` اضافه می‌کند
- Install required Composer packages
- پکیج‌های مورد نیاز Composer را نصب می‌کند
- Clear Laravel cache
- کش Laravel را پاک می‌کند

### Step 2: Manual Installation (Alternative)
### مرحله 2: نصب دستی (جایگزین)

If you prefer to set up manually:
اگر ترجیح می‌دهید به صورت دستی راه‌اندازی کنید:

#### 2.1 Install Packages
#### 2.1 نصب پکیج‌ها

```bash
composer require open-telemetry/sdk open-telemetry/exporter-otlp open-telemetry/sem-conv
```

#### 2.2 Configure Environment
#### 2.2 تنظیم محیط

Add to your `.env` file:
به فایل `.env` خود اضافه کنید:

```env
OTEL_ENABLED=true
OTEL_EXPORTER_OTLP_ENDPOINT=http://localhost:4317
OTEL_EXPORTER_OTLP_PROTOCOL=grpc
OTEL_SERVICE_NAME=hooshex
OTEL_ENVIRONMENT=test1
OTEL_TEAM=test2
OTEL_BEAUTY_BOOKING_ENABLED=true
OTEL_SAMPLING_RATE=1.0
```

#### 2.3 Clear Cache
#### 2.3 پاک کردن کش

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 3: Verify Setup
### مرحله 3: تأیید راه‌اندازی

#### 3.1 Check Observe Agent
#### 3.1 بررسی Observe Agent

```bash
# Check status
# بررسی وضعیت
sudo systemctl status observe-agent

# View statistics
# مشاهده آمار
observe-agent status
```

#### 3.2 Test Application
#### 3.2 تست برنامه

1. Make a booking through the Beauty Booking module
   یک رزرو از طریق ماژول Beauty Booking انجام دهید

2. Check Laravel logs for OpenTelemetry activity:
   لاگ‌های Laravel را برای فعالیت OpenTelemetry بررسی کنید:
   ```bash
   tail -f storage/logs/laravel.log | grep -i opentelemetry
   ```

3. View traces in Observe dashboard
   traceها را در داشبورد Observe مشاهده کنید

## Environment Variables Reference
## مرجع متغیرهای محیطی

| Variable | Description | Default |
|----------|-------------|---------|
| `OTEL_ENABLED` | Enable/disable OpenTelemetry | `false` |
| `OTEL_EXPORTER_OTLP_ENDPOINT` | OTLP endpoint URL | `http://localhost:4317` |
| `OTEL_EXPORTER_OTLP_PROTOCOL` | Protocol (grpc/http/protobuf) | `grpc` |
| `OTEL_SERVICE_NAME` | Service name in Observe | `6ammart-laravel` |
| `OTEL_ENVIRONMENT` | Deployment environment | `production` |
| `OTEL_TEAM` | Team name | `default` |
| `OTEL_BEAUTY_BOOKING_ENABLED` | Enable Beauty Booking instrumentation | `true` |
| `OTEL_SAMPLING_RATE` | Trace sampling rate (0.0-1.0) | `1.0` |

## Troubleshooting
## عیب‌یابی

### Issue: Traces not appearing
### مشکل: traceها ظاهر نمی‌شوند

**Solution:**
**راه حل:**

1. Verify Observe Agent is running:
   بررسی کنید که Observe Agent در حال اجرا است:
   ```bash
   sudo systemctl status observe-agent
   ```

2. Check agent logs:
   لاگ‌های agent را بررسی کنید:
   ```bash
   sudo journalctl -u observe-agent -f
   ```

3. Verify environment variables:
   متغیرهای محیطی را بررسی کنید:
   ```bash
   php artisan tinker
   >>> config('opentelemetry.enabled')
   >>> config('opentelemetry.endpoint')
   ```

### Issue: Connection refused
### مشکل: اتصال رد شد

**Solution:**
**راه حل:**

1. Check if Observe Agent is listening:
   بررسی کنید که Observe Agent در حال گوش دادن است:
   ```bash
   sudo netstat -tlnp | grep 4317
   ```

2. Verify firewall settings:
   تنظیمات فایروال را بررسی کنید:
   ```bash
   sudo ufw status
   ```

3. Check endpoint URL in `.env`:
   URL endpoint را در `.env` بررسی کنید

### Issue: Package installation fails
### مشکل: نصب پکیج ناموفق است

**Solution:**
**راه حل:**

1. Update Composer:
   به‌روزرسانی Composer:
   ```bash
   composer self-update
   ```

2. Clear Composer cache:
   پاک کردن کش Composer:
   ```bash
   composer clear-cache
   ```

3. Try installing packages individually:
   تلاش برای نصب پکیج‌ها به صورت جداگانه:
   ```bash
   composer require open-telemetry/sdk
   composer require open-telemetry/exporter-otlp
   composer require open-telemetry/sem-conv
   ```

## Next Steps
## مراحل بعدی

1. **Review Documentation**: See `Modules/BeautyBooking/Documentation/OPENTELEMETRY_SETUP.md` for detailed information
   **بررسی مستندات**: برای اطلاعات دقیق‌تر به `Modules/BeautyBooking/Documentation/OPENTELEMETRY_SETUP.md` مراجعه کنید

2. **Customize Instrumentation**: Add custom spans to your code using the `OpenTelemetryInstrumentation` trait
   **سفارشی‌سازی ابزارسازی**: spanهای سفارشی را با استفاده از trait `OpenTelemetryInstrumentation` به کد خود اضافه کنید

3. **Monitor Performance**: Adjust sampling rate for production environments
   **نظارت بر عملکرد**: نرخ نمونه‌برداری را برای محیط‌های production تنظیم کنید

## Support
## پشتیبانی

For detailed information, see:
برای اطلاعات دقیق‌تر، ببینید:

- `Modules/BeautyBooking/Documentation/OPENTELEMETRY_SETUP.md` - Full setup guide
- `config/opentelemetry.php` - Configuration file
- [OpenTelemetry PHP Documentation](https://opentelemetry.io/docs/instrumentation/php/)
- [Observe Agent Documentation](https://docs.observeinc.com/)

