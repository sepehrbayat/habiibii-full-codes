# Testing Guide - Beauty Booking with Observe Agent
# راهنمای تست - رزرو زیبایی با Observe Agent

## ✅ Setup Verification Complete
## ✅ تأیید راه‌اندازی کامل شد

All components are configured and ready. Use this guide to test the integration.

تمام اجزا پیکربندی شده و آماده هستند. از این راهنما برای تست یکپارچه‌سازی استفاده کنید.

## Quick Verification
## تأیید سریع

Run the verification script:
اجرای اسکریپت تأیید:

```bash
php verify-opentelemetry.php
```

This will:
این کار را انجام می‌دهد:

- ✅ Check OpenTelemetry configuration
- ✅ بررسی تنظیمات OpenTelemetry
- ✅ Verify Tracer Provider is initialized
- ✅ تأیید راه‌اندازی Tracer Provider
- ✅ Create a test span
- ✅ ایجاد یک span تست
- ✅ Check Observe Agent status
- ✅ بررسی وضعیت Observe Agent

## Testing with Real Bookings
## تست با رزروهای واقعی

### Prerequisites
### پیش‌نیازها

Before testing, ensure you have:
قبل از تست، اطمینان حاصل کنید که:

1. **Verified Salon**: A salon with `is_verified = true`
   **سالن تأیید شده**: یک سالن با `is_verified = true`

2. **Active Service**: A service with `status = 1` for the salon
   **خدمت فعال**: یک خدمت با `status = 1` برای سالن

3. **User Account**: An authenticated user
   **حساب کاربری**: یک کاربر احراز هویت شده

### Method 1: API Request
### روش 1: درخواست API

```bash
# Get authentication token first
TOKEN="your-auth-token"

# Create booking via API
curl -X POST http://your-domain/api/v1/beautybooking/bookings \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "salon_id": 1,
    "service_id": 1,
    "booking_date": "2024-12-01",
    "booking_time": "10:00",
    "payment_method": "cash_payment"
  }'
```

### Method 2: Laravel Tinker
### روش 2: Laravel Tinker

```bash
php artisan tinker
```

Then:
سپس:

```php
$user = App\Models\User::first();
$salon = Modules\BeautyBooking\Entities\BeautySalon::where('is_verified', true)->first();
$service = Modules\BeautyBooking\Entities\BeautyService::where('salon_id', $salon->id)->first();

$bookingService = app(Modules\BeautyBooking\Services\BeautyBookingService::class);

$booking = $bookingService->createBooking(
    $user->id,
    $salon->id,
    [
        'service_id' => $service->id,
        'booking_date' => now()->addDay()->format('Y-m-d'),
        'booking_time' => '10:00',
        'payment_method' => 'cash_payment',
    ]
);
```

### Method 3: Web Interface
### روش 3: رابط وب

1. Navigate to Beauty Booking module in your application
   به ماژول Beauty Booking در برنامه خود بروید

2. Create a booking through the booking wizard
   یک رزرو از طریق ویزارد رزرو ایجاد کنید

3. Complete the booking process
   فرآیند رزرو را تکمیل کنید

## Monitoring Traces
## نظارت بر Traceها

### Check Observe Agent Statistics
### بررسی آمار Observe Agent

```bash
# View current status
observe-agent status

# Watch for new traces (run in separate terminal)
watch -n 2 'observe-agent status | grep -A 5 "Traces Stats"'
```

### Check Laravel Logs
### بررسی لاگ‌های Laravel

```bash
# Watch for OpenTelemetry activity
tail -f storage/logs/laravel.log | grep -i opentelemetry

# Watch for booking creation
tail -f storage/logs/laravel.log | grep -i booking
```

### Check Observe Agent Logs
### بررسی لاگ‌های Observe Agent

```bash
# View recent logs
sudo journalctl -u observe-agent --since "10 minutes ago" --no-pager

# Follow logs in real-time
sudo journalctl -u observe-agent -f
```

## Viewing Traces in Observe Dashboard
## مشاهده Traceها در داشبورد Observe

1. **Log in to Observe**: Access your Observe instance
   **ورود به Observe**: به نمونه Observe خود دسترسی پیدا کنید

2. **Navigate to Trace Explorer**: Go to APM → Trace Explorer
   **رفتن به Trace Explorer**: به APM → Trace Explorer بروید

3. **Filter by Service**: Filter traces by service name `hooshex`
   **فیلتر بر اساس سرویس**: traceها را بر اساس نام سرویس `hooshex` فیلتر کنید

4. **Search for Operations**: Look for `beauty.booking.create`
   **جستجوی عملیات**: به دنبال `beauty.booking.create` باشید

5. **View Span Details**: Click on a trace to see:
   **مشاهده جزئیات Span**: روی یک trace کلیک کنید تا ببینید:
   - Operation name: `beauty.booking.create`
   - Attributes:
     - `beauty.booking.salon_id`
     - `beauty.booking.service_id`
     - `beauty.booking.user_id`
     - `beauty.booking.staff_id` (if provided)
   - Duration
   - Status (OK/ERROR)

## Expected Trace Structure
## ساختار Trace مورد انتظار

When a booking is created, you should see:

وقتی یک رزرو ایجاد می‌شود، باید ببینید:

```
Trace:
  └─ beauty.booking.create (span)
     ├─ Attributes:
     │  ├─ beauty.booking.salon_id: 1
     │  ├─ beauty.booking.service_id: 1
     │  ├─ beauty.booking.user_id: 1
     │  └─ beauty.booking.staff_id: null (optional)
     ├─ Duration: ~XXXms
     └─ Status: OK
```

## Troubleshooting
## عیب‌یابی

### No Traces Appearing
### Traceها ظاهر نمی‌شوند

1. **Check OpenTelemetry is enabled**:
   ```bash
   php artisan tinker --execute="echo config('opentelemetry.enabled') ? 'YES' : 'NO';"
   ```

2. **Verify endpoint is correct**:
   ```bash
   curl http://localhost:4318/v1/traces
   ```

3. **Check service provider loaded**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

4. **Verify Observe Agent is receiving**:
   ```bash
   observe-agent status | grep "Traces Stats"
   ```

### Traces Appear But No Data
### Traceها ظاهر می‌شوند اما داده‌ای ندارند

1. **Check span attributes are set**:
   - Verify `BeautyBookingService::createBooking()` is using the instrumentation trait
   - Check that attributes are being added to spans

2. **Verify service provider initialization**:
   ```bash
   tail -f storage/logs/laravel.log | grep -i "OpenTelemetry initialized"
   ```

### Connection Issues
### مشکلات اتصال

1. **Check ports are listening**:
   ```bash
   sudo netstat -tlnp | grep -E "4317|4318"
   ```

2. **Verify firewall**:
   ```bash
   sudo ufw status
   ```

3. **Test endpoint**:
   ```bash
   curl -v http://localhost:4318
   ```

## Performance Considerations
## ملاحظات عملکرد

### Batch Processing
### پردازش Batch

Spans are batched by `BatchSpanProcessor`:
Spanها توسط `BatchSpanProcessor` batch می‌شوند:

- Default batch size: 512 spans
- Spans may not appear immediately
- Wait 2-5 seconds after creating booking
- Check Observe dashboard after a short delay

### Sampling Rate
### نرخ نمونه‌برداری

Current sampling rate: `1.0` (100%)

For production, consider reducing:
برای production، کاهش را در نظر بگیرید:

```env
OTEL_SAMPLING_RATE=0.1  # Sample 10% of traces
```

## Success Indicators
## شاخص‌های موفقیت

You'll know it's working when:
می‌دانید که کار می‌کند وقتی:

✅ `observe-agent status` shows increasing `ReceiverAcceptedCount`
✅ Traces appear in Observe dashboard
✅ Span attributes are visible (salon_id, service_id, etc.)
✅ Trace duration matches booking creation time
✅ No errors in Laravel or Observe Agent logs

## Next Steps After Verification
## مراحل بعدی پس از تأیید

1. **Add More Instrumentation**: Instrument other operations
   **افزودن ابزارسازی بیشتر**: عملیات دیگر را ابزارسازی کنید

2. **Create Dashboards**: Build dashboards in Observe
   **ایجاد داشبوردها**: داشبوردها را در Observe بسازید

3. **Set Up Alerts**: Create monitors for booking failures
   **تنظیم هشدارها**: مانیتور برای خطاهای رزرو ایجاد کنید

4. **Optimize**: Adjust sampling rate for production
   **بهینه‌سازی**: نرخ نمونه‌برداری را برای production تنظیم کنید

---

**Setup is complete and ready for testing!** 🎉

**راه‌اندازی کامل است و آماده تست است!** 🎉

