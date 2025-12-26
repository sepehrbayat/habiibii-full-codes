# Beauty Booking Module - Complete Test Suite
# ماژول Beauty Booking - مجموعه تست کامل

## Overview
## بررسی کلی

This directory contains comprehensive test suites for the Beauty Booking module with Observe Agent monitoring.

این دایرکتوری شامل مجموعه تست‌های جامع برای ماژول Beauty Booking با نظارت Observe Agent است.

## Test Files
## فایل‌های تست

### 1. `beauty-booking-complete-tests.php`
**Purpose**: Complete test suite for all Beauty Booking features
**هدف**: مجموعه تست کامل برای تمام ویژگی‌های Beauty Booking

**Tests Covered**:
- ✅ Salon Search
- ✅ Get Salon Details
- ✅ Get Service Categories
- ✅ Check Availability
- ✅ Create Booking
- ✅ Get Booking Details
- ✅ List User Bookings
- ✅ Create Review
- ✅ Get Salon Reviews
- ✅ Service Suggestions (Cross-Selling)
- ✅ Get Popular Salons
- ✅ Get Top Rated Salons
- ✅ Calculate Ranking
- ✅ Get Ranked Salons
- ✅ Get Packages
- ✅ Get Gift Cards

### 2. `beauty-booking-full-test-suite.php`
**Purpose**: Extended test suite with detailed error handling
**هدف**: مجموعه تست گسترده با مدیریت خطای تفصیلی

## Running Tests
## اجرای تست‌ها

### Quick Test
### تست سریع

```bash
php tests/beauty-booking-complete-tests.php
```

### Full Test Suite
### مجموعه تست کامل

```bash
php tests/beauty-booking-full-test-suite.php
```

## Prerequisites
## پیش‌نیازها

1. **Test Data**: Run `create-test-data.php` first
   **داده‌های تست**: ابتدا `create-test-data.php` را اجرا کنید

2. **Observe Agent**: Must be running
   **Observe Agent**: باید در حال اجرا باشد

3. **OpenTelemetry**: Must be enabled
   **OpenTelemetry**: باید فعال باشد

## What Gets Tested
## چه چیزهایی تست می‌شوند

### Booking Operations
### عملیات رزرو

- Create booking with OpenTelemetry tracing
- Get booking details
- List user bookings
- Cancel booking

### Salon Operations
### عملیات سالن

- Search salons
- Get salon details
- Popular salons
- Top rated salons
- Ranking calculations

### Service Operations
### عملیات خدمت

- Get service categories
- Check availability
- Service suggestions (cross-selling)

### Review Operations
### عملیات نظر

- Create review
- Get salon reviews

### Additional Features
### ویژگی‌های اضافی

- Packages
- Gift cards
- Loyalty points (if implemented)

## Observe Agent Monitoring
## نظارت Observe Agent

All tests monitor Observe Agent for traces:
تمام تست‌ها Observe Agent را برای traceها نظارت می‌کنند:

- Initial trace count is recorded
- After each test, trace count is checked
- Final summary shows total traces generated
- All operations should generate OpenTelemetry spans

## Expected Output
## خروجی مورد انتظار

```
========================================
Beauty Booking Complete Test Suite
========================================

Initial trace count: 0

✓ Salon Search
  Found 1 salons

✓ Get Salon Details
  Salon ID: 1

...

========================================
Test Summary
========================================
Initial Traces: 0
Final Traces: X
New Traces: X

✅ Test Suite Complete!
```

## Troubleshooting
## عیب‌یابی

### No Traces Appearing
### Traceها ظاهر نمی‌شوند

1. Check OpenTelemetry is enabled:
   ```bash
   php artisan tinker --execute="echo config('opentelemetry.enabled') ? 'YES' : 'NO';"
   ```

2. Verify Observe Agent is running:
   ```bash
   observe-agent status
   ```

3. Check service provider is loaded:
   ```bash
   tail -f storage/logs/laravel.log | grep -i opentelemetry
   ```

### Tests Failing
### تست‌ها ناموفق

1. Ensure test data exists:
   ```bash
   php create-test-data.php
   ```

2. Check database connections
3. Verify all migrations are run

## Notes
## یادداشت‌ها

- Spans are batched by `BatchSpanProcessor`
- May take a few seconds to appear in Observe Agent stats
- Check Observe dashboard for detailed trace information
- All operations are instrumented with OpenTelemetry

## Next Steps
## مراحل بعدی

1. Run tests regularly to monitor system health
2. Check Observe dashboard for trace details
3. Add more tests as new features are added
4. Monitor trace generation performance

