# OpenTelemetry Setup for Beauty Booking Module
# راه‌اندازی OpenTelemetry برای ماژول Beauty Booking

This guide explains how to set up OpenTelemetry instrumentation for the Beauty Booking module to send traces, metrics, and logs to the Observe Agent.

این راهنما نحوه راه‌اندازی ابزارسازی OpenTelemetry برای ماژول Beauty Booking را برای ارسال traceها، متریک‌ها و لاگ‌ها به Observe Agent توضیح می‌دهد.

## Prerequisites
## پیش‌نیازها

1. **Observe Agent Installed**: The Observe Agent must be installed and running on your Linux server.
   **Observe Agent نصب شده**: Observe Agent باید روی سرور Linux شما نصب و در حال اجرا باشد.

2. **Ingest Token**: You need a valid ingest token from Observe.
   **توکن Ingest**: شما به یک توکن ingest معتبر از Observe نیاز دارید.

## Installation Steps
## مراحل نصب

### 1. Install OpenTelemetry PHP Packages
### 1. نصب پکیج‌های OpenTelemetry PHP

Run the following command to install OpenTelemetry packages:
دستور زیر را برای نصب پکیج‌های OpenTelemetry اجرا کنید:

```bash
composer require open-telemetry/opentelemetry open-telemetry/opentelemetry-php open-telemetry/sdk open-telemetry/exporter-otlp open-telemetry/sem-conv
```

### 2. Configure Environment Variables
### 2. تنظیم متغیرهای محیطی

Add the following environment variables to your `.env` file:
متغیرهای محیطی زیر را به فایل `.env` خود اضافه کنید:

```env
# OpenTelemetry Configuration
# تنظیمات OpenTelemetry

# Enable OpenTelemetry
# فعال کردن OpenTelemetry
OTEL_ENABLED=true

# OTLP Endpoint (Observe Agent)
# OTLP Endpoint (Observe Agent)
# For gRPC: http://localhost:4317
# For HTTP/Protobuf: http://localhost:4318
OTEL_EXPORTER_OTLP_ENDPOINT=http://localhost:4317

# OTLP Protocol
# OTLP Protocol
# Options: grpc, http/protobuf, http/json
OTEL_EXPORTER_OTLP_PROTOCOL=grpc

# Service Name (matches your Observe Agent configuration)
# نام سرویس (مطابق با تنظیمات Observe Agent شما)
OTEL_SERVICE_NAME=hooshex

# Environment (matches your Observe Agent configuration)
# محیط (مطابق با تنظیمات Observe Agent شما)
OTEL_ENVIRONMENT=test1

# Team Name (matches your Observe Agent configuration)
# نام تیم (مطابق با تنظیمات Observe Agent شما)
OTEL_TEAM=test2

# Beauty Booking Module Specific Settings
# تنظیمات خاص ماژول Beauty Booking
OTEL_BEAUTY_BOOKING_ENABLED=true
OTEL_BEAUTY_BOOKING_TRACE_QUERIES=true
OTEL_BEAUTY_BOOKING_TRACE_REQUESTS=true
OTEL_BEAUTY_BOOKING_TRACE_SERVICES=true

# Sampling Rate (0.0 to 1.0)
# نرخ نمونه‌برداری (0.0 تا 1.0)
# 1.0 = 100% (sample all traces)
# 0.1 = 10% (sample 10% of traces)
OTEL_SAMPLING_RATE=1.0
```

### 3. Verify Observe Agent is Running
### 3. بررسی اجرای Observe Agent

Check that the Observe Agent is running:
بررسی کنید که Observe Agent در حال اجرا است:

```bash
sudo systemctl status observe-agent
```

Check agent statistics:
بررسی آمار agent:

```bash
observe-agent status
```

### 4. Clear Laravel Cache
### 4. پاک کردن کش Laravel

After configuration, clear Laravel cache:
پس از تنظیمات، کش Laravel را پاک کنید:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## How It Works
## نحوه کار

### Automatic Instrumentation
### ابزارسازی خودکار

The Beauty Booking module automatically instruments the following operations:
ماژول Beauty Booking به طور خودکار عملیات زیر را ابزارسازی می‌کند:

1. **Booking Creation**: All booking creation operations are traced with detailed attributes.
   **ایجاد رزرو**: تمام عملیات ایجاد رزرو با ویژگی‌های دقیق trace می‌شوند.

2. **Service Operations**: Service method calls are automatically instrumented.
   **عملیات سرویس**: فراخوانی‌های متد سرویس به طور خودکار ابزارسازی می‌شوند.

3. **Database Queries**: Database operations can be traced (if enabled).
   **کوئری‌های دیتابیس**: عملیات دیتابیس می‌توانند trace شوند (در صورت فعال بودن).

### Manual Instrumentation
### ابزارسازی دستی

You can manually instrument custom code using the `OpenTelemetryInstrumentation` trait:
شما می‌توانید کد سفارشی را با استفاده از trait `OpenTelemetryInstrumentation` به صورت دستی ابزارسازی کنید:

```php
use Modules\BeautyBooking\Traits\OpenTelemetryInstrumentation;

class MyService
{
    use OpenTelemetryInstrumentation;

    public function myMethod()
    {
        return $this->instrument('my.operation', function () {
            // Your code here
            // کد شما اینجا
            return $result;
        }, [
            'custom.attribute' => 'value',
        ]);
    }
}
```

## Viewing Traces in Observe
## مشاهده Traceها در Observe

1. **Log in to Observe**: Access your Observe dashboard.
   **ورود به Observe**: به داشبورد Observe خود دسترسی پیدا کنید.

2. **Navigate to Traces**: Go to the Traces section.
   **رفتن به Traceها**: به بخش Traceها بروید.

3. **Filter by Service**: Filter traces by service name `hooshex`.
   **فیلتر بر اساس سرویس**: traceها را بر اساس نام سرویس `hooshex` فیلتر کنید.

4. **View Beauty Booking Operations**: Look for spans with `beauty.booking.*` operation names.
   **مشاهده عملیات Beauty Booking**: به دنبال spanهایی با نام عملیات `beauty.booking.*` باشید.

## Troubleshooting
## عیب‌یابی

### Traces Not Appearing
### Traceها ظاهر نمی‌شوند

1. **Check Observe Agent Status**:
   **بررسی وضعیت Observe Agent**:
   ```bash
   sudo systemctl status observe-agent
   ```

2. **Check Agent Logs**:
   **بررسی لاگ‌های Agent**:
   ```bash
   sudo journalctl -u observe-agent -f
   ```

3. **Verify Environment Variables**:
   **بررسی متغیرهای محیطی**:
   ```bash
   php artisan tinker
   >>> config('opentelemetry.enabled')
   >>> config('opentelemetry.endpoint')
   ```

4. **Check Laravel Logs**:
   **بررسی لاگ‌های Laravel**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Connection Issues
### مشکلات اتصال

If you see connection errors, verify:
اگر خطاهای اتصال می‌بینید، بررسی کنید:

1. **Observe Agent is listening on correct port**:
   **Observe Agent روی پورت صحیح در حال گوش دادن است**:
   ```bash
   sudo netstat -tlnp | grep 4317
   ```

2. **Firewall allows connections**:
   **فایروال اجازه اتصال می‌دهد**:
   ```bash
   sudo ufw status
   ```

3. **Endpoint URL is correct**:
   **URL endpoint صحیح است**:
   - For gRPC: `http://localhost:4317`
   - For HTTP/Protobuf: `http://localhost:4318`

## Performance Considerations
## ملاحظات عملکرد

### Sampling Rate
### نرخ نمونه‌برداری

For production environments, consider reducing the sampling rate to avoid performance impact:
برای محیط‌های production، کاهش نرخ نمونه‌برداری را برای جلوگیری از تأثیر عملکرد در نظر بگیرید:

```env
OTEL_SAMPLING_RATE=0.1  # Sample 10% of traces
```

### Batch Size
### اندازه Batch

The default batch size is 512 spans. You can adjust this in `config/opentelemetry.php`:
اندازه batch پیش‌فرض 512 span است. می‌توانید این را در `config/opentelemetry.php` تنظیم کنید:

```php
'batch_size' => env('OTEL_BATCH_SIZE', 512),
```

## Additional Resources
## منابع اضافی

- [OpenTelemetry PHP Documentation](https://opentelemetry.io/docs/instrumentation/php/)
- [Observe Agent Documentation](https://docs.observeinc.com/)
- [OTLP Protocol Specification](https://opentelemetry.io/docs/specs/otlp/)

## Support
## پشتیبانی

For issues or questions:
برای مشکلات یا سوالات:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check Observe Agent logs: `sudo journalctl -u observe-agent`
3. Review OpenTelemetry configuration: `config/opentelemetry.php`

