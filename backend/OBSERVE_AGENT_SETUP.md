# Observe Agent Setup - Following Official Documentation
# راه‌اندازی Observe Agent - طبق مستندات رسمی

## Official Documentation Reference
## مرجع مستندات رسمی

This setup follows the official Observe documentation:
این راه‌اندازی طبق مستندات رسمی Observe است:

**Source**: [Send PHP application data to Observe](https://docs.observeinc.com/en/latest/content/send-data/apm-instrumentation/php.html)

## Configuration Updates
## به‌روزرسانی‌های تنظیمات

### Environment Variables (Updated per Documentation)
### متغیرهای محیطی (به‌روزرسانی شده طبق مستندات)

```env
# Basic Configuration
OTEL_ENABLED=true
OTEL_SERVICE_NAME=hooshex
OTEL_RESOURCE_ATTRIBUTES=deployment.environment=test1

# OTLP Exporter (HTTP/Protobuf on port 4318)
OTEL_EXPORTER_OTLP_ENDPOINT=http://localhost:4318
OTEL_EXPORTER_OTLP_PROTOCOL=http/protobuf

# Exporters
OTEL_TRACES_EXPORTER=otlp
OTEL_METRICS_EXPORTER=otlp
OTEL_LOGS_EXPORTER=otlp

# PHP Auto-loading
OTEL_PHP_AUTOLOAD_ENABLED=true

# Beauty Booking Module
OTEL_BEAUTY_BOOKING_ENABLED=true
OTEL_SAMPLING_RATE=1.0
```

## Key Differences from Initial Setup
## تفاوت‌های کلیدی با راه‌اندازی اولیه

### Port Change
### تغییر پورت

- **Before**: Port `4317` (gRPC)
- **After**: Port `4318` (HTTP/Protobuf) ✅

According to Observe documentation, PHP applications should use HTTP/Protobuf on port 4318.

طبق مستندات Observe، برنامه‌های PHP باید از HTTP/Protobuf روی پورت 4318 استفاده کنند.

### Protocol Change
### تغییر پروتکل

- **Before**: `grpc`
- **After**: `http/protobuf` ✅

### Additional Environment Variables
### متغیرهای محیطی اضافی

Added per official documentation:
اضافه شده طبق مستندات رسمی:

- `OTEL_TRACES_EXPORTER=otlp`
- `OTEL_METRICS_EXPORTER=otlp`
- `OTEL_LOGS_EXPORTER=otlp`
- `OTEL_PHP_AUTOLOAD_ENABLED=true`
- `OTEL_RESOURCE_ATTRIBUTES=deployment.environment=test1`

## OpenTelemetry PHP Extension (Optional)
## افزونه OpenTelemetry PHP (اختیاری)

According to the documentation, for **auto-instrumentation**, you need to install the OpenTelemetry PHP extension:

طبق مستندات، برای **ابزارسازی خودکار**، باید افزونه OpenTelemetry PHP را نصب کنید:

```bash
pecl install opentelemetry
```

Then add to `php.ini`:
سپس به `php.ini` اضافه کنید:

```ini
extension=opentelemetry.so
```

**Note**: For **manual instrumentation** (which we're using), the extension is **optional**. Our current setup works without it.

**توجه**: برای **ابزارسازی دستی** (که ما استفاده می‌کنیم)، افزونه **اختیاری** است. راه‌اندازی فعلی ما بدون آن کار می‌کند.

## Verify Setup
## تأیید راه‌اندازی

### 1. Check Environment Variables
### 1. بررسی متغیرهای محیطی

```bash
php artisan tinker --execute="var_dump(config('opentelemetry'));"
```

### 2. Check Observe Agent
### 2. بررسی Observe Agent

```bash
observe-agent status
```

The agent should be listening on port 4318 for HTTP/Protobuf connections.

agent باید روی پورت 4318 برای اتصالات HTTP/Protobuf در حال گوش دادن باشد.

### 3. Test Application
### 3. تست برنامه

1. Create a booking through Beauty Booking module
   یک رزرو از طریق ماژول Beauty Booking ایجاد کنید

2. Check traces in Observe dashboard
   traceها را در داشبورد Observe بررسی کنید

3. Monitor logs:
   نظارت بر لاگ‌ها:
   ```bash
   tail -f storage/logs/laravel.log
   ```

## Current Implementation
## پیاده‌سازی فعلی

We're using **manual instrumentation** which:
ما از **ابزارسازی دستی** استفاده می‌کنیم که:

✅ Works without the OpenTelemetry PHP extension
✅ بدون افزونه OpenTelemetry PHP کار می‌کند

✅ Uses the OpenTelemetry SDK directly
✅ مستقیماً از OpenTelemetry SDK استفاده می‌کند

✅ Provides fine-grained control over what gets traced
✅ کنترل دقیق بر روی آنچه trace می‌شود را فراهم می‌کند

✅ Already integrated in `BeautyBookingService`
✅ قبلاً در `BeautyBookingService` یکپارچه شده است

## Auto-Instrumentation (Alternative)
## ابزارسازی خودکار (جایگزین)

If you want to use **auto-instrumentation** (automatic tracing of all HTTP requests, database queries, etc.), you would need to:

اگر می‌خواهید از **ابزارسازی خودکار** استفاده کنید (trace خودکار تمام درخواست‌های HTTP، کوئری‌های دیتابیس و غیره)، باید:

1. Install the OpenTelemetry PHP extension:
   ```bash
   pecl install opentelemetry
   ```

2. Install auto-instrumentation packages:
   ```bash
   composer require \
       open-telemetry/opentelemetry-auto-slim \
       open-telemetry/opentelemetry-auto-psr18
   ```

3. Enable auto-loading in `.env`:
   ```env
   OTEL_PHP_AUTOLOAD_ENABLED=true
   ```

## Documentation Links
## لینک‌های مستندات

- [Observe PHP Instrumentation](https://docs.observeinc.com/en/latest/content/send-data/apm-instrumentation/php.html)
- [OpenTelemetry PHP Documentation](https://opentelemetry.io/docs/languages/php/instrumentation/)
- [OpenTelemetry PHP Auto-Instrumentation](https://opentelemetry.io/docs/zero-code/php/)

## Summary
## خلاصه

✅ **Configuration Updated**: Port 4318, HTTP/Protobuf protocol
✅ **Environment Variables**: All required variables added
✅ **Service Provider**: Configured to use correct endpoint
✅ **Manual Instrumentation**: Working without PHP extension
✅ **Observe Agent**: Running and ready

**Setup is complete and follows official Observe documentation!** 🎉

**راه‌اندازی کامل است و طبق مستندات رسمی Observe است!** 🎉

