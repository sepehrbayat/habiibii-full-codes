<?php

/**
 * Test Booking Creation with Observe Agent Monitoring
 * تست ایجاد رزرو با نظارت Observe Agent
 * 
 * This script creates a booking and monitors Observe Agent for traces
 * این اسکریپت یک رزرو ایجاد می‌کند و Observe Agent را برای traceها نظارت می‌کند
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\BeautyBooking\Services\BeautyBookingService;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use App\Models\User;

echo "========================================\n";
echo "Beauty Booking Test with Observe Agent\n";
echo "تست رزرو زیبایی با Observe Agent\n";
echo "========================================\n\n";

try {
    // Step 1: Verify OpenTelemetry
    echo "Step 1: Verifying OpenTelemetry...\n";
    echo "مرحله 1: تأیید OpenTelemetry...\n";
    
    $enabled = config('opentelemetry.enabled', false);
    if (!$enabled) {
        echo "❌ OpenTelemetry is DISABLED!\n";
        echo "❌ OpenTelemetry غیرفعال است!\n";
        exit(1);
    }
    
    echo "✓ OpenTelemetry: ENABLED\n";
    echo "✓ Endpoint: " . config('opentelemetry.endpoint') . "\n";
    echo "✓ Service: " . config('opentelemetry.service_name') . "\n\n";
    
    // Step 2: Get test data
    echo "Step 2: Getting test data...\n";
    echo "مرحله 2: دریافت داده‌های تست...\n";
    
    $salon = BeautySalon::where('is_verified', true)->first();
    if (!$salon) {
        echo "❌ No verified salon found. Run create-test-data.php first.\n";
        echo "❌ سالن تأیید شده پیدا نشد. ابتدا create-test-data.php را اجرا کنید.\n";
        exit(1);
    }
    
    $service = BeautyService::where('salon_id', $salon->id)
        ->where('status', 1)
        ->first();
    
    if (!$service) {
        echo "❌ No active service found. Run create-test-data.php first.\n";
        echo "❌ خدمت فعال پیدا نشد. ابتدا create-test-data.php را اجرا کنید.\n";
        exit(1);
    }
    
    $user = User::first();
    if (!$user) {
        echo "❌ No user found. Run create-test-data.php first.\n";
        echo "❌ کاربری پیدا نشد. ابتدا create-test-data.php را اجرا کنید.\n";
        exit(1);
    }
    
    echo "✓ Salon: ID {$salon->id}\n";
    echo "✓ Service: ID {$service->id} - {$service->name}\n";
    echo "✓ User: ID {$user->id}\n\n";
    
    // Step 3: Check Observe Agent BEFORE
    echo "Step 3: Checking Observe Agent (BEFORE)...\n";
    echo "مرحله 3: بررسی Observe Agent (قبل)...\n";
    
    $beforeStats = shell_exec('observe-agent status 2>&1');
    $beforeTraceCount = 0;
    
    if (preg_match('/Traces Stats.*?ReceiverAcceptedCount:\s*(\d+)/s', $beforeStats, $matches)) {
        $beforeTraceCount = (int)$matches[1];
    }
    
    echo "  Traces received before: {$beforeTraceCount}\n";
    echo "  traceهای دریافت شده قبل: {$beforeTraceCount}\n\n";
    
    // Step 4: Create booking
    echo "Step 4: Creating booking...\n";
    echo "مرحله 4: ایجاد رزرو...\n";
    
    $tomorrow = now()->addDay()->format('Y-m-d');
    $bookingTime = '10:00';
    
    $bookingData = [
        'service_id' => $service->id,
        'booking_date' => $tomorrow,
        'booking_time' => $bookingTime,
        'payment_method' => 'cash_payment',
        'notes' => 'Test booking for Observe Agent trace verification',
    ];
    
    echo "  Date: {$tomorrow}\n";
    echo "  Time: {$bookingTime}\n";
    echo "  Payment: cash_payment\n\n";
    
    $bookingService = app(BeautyBookingService::class);
    
    echo "Calling createBooking()...\n";
    echo "فراخوانی createBooking()...\n";
    
    $startTime = microtime(true);
    
    $booking = $bookingService->createBooking(
        $user->id,
        $salon->id,
        $bookingData
    );
    
    $endTime = microtime(true);
    $duration = round(($endTime - $startTime) * 1000, 2);
    
    echo "✓ Booking created successfully!\n";
    echo "✓ رزرو با موفقیت ایجاد شد!\n";
    echo "  Booking ID: {$booking->id}\n";
    echo "  Booking Reference: {$booking->booking_reference}\n";
    echo "  Status: {$booking->status}\n";
    echo "  Total Amount: {$booking->total_amount}\n";
    echo "  Duration: {$duration}ms\n\n";
    
    // Step 5: Wait for spans to be sent
    echo "Step 5: Waiting for spans to be sent...\n";
    echo "مرحله 5: در انتظار ارسال spanها...\n";
    echo "  (BatchSpanProcessor batches spans, may take a few seconds)\n";
    echo "  (BatchSpanProcessor spanها را batch می‌کند، ممکن است چند ثانیه طول بکشد)\n";
    
    // Wait and check multiple times
    for ($i = 1; $i <= 5; $i++) {
        sleep(2);
        $currentStats = shell_exec('observe-agent status 2>&1');
        
        if (preg_match('/Traces Stats.*?ReceiverAcceptedCount:\s*(\d+)/s', $currentStats, $currentMatches)) {
            $currentTraceCount = (int)$currentMatches[1];
            echo "  Check {$i}/5: Traces received: {$currentTraceCount}\n";
            
            if ($currentTraceCount > $beforeTraceCount) {
                echo "\n✅ SUCCESS: New traces detected!\n";
                echo "✅ موفقیت: traceهای جدید تشخیص داده شدند!\n";
                echo "  New traces: " . ($currentTraceCount - $beforeTraceCount) . "\n";
                echo "  traceهای جدید: " . ($currentTraceCount - $beforeTraceCount) . "\n";
                break;
            }
        }
    }
    
    // Step 6: Final check
    echo "\nStep 6: Final check...\n";
    echo "مرحله 6: بررسی نهایی...\n";
    
    $finalStats = shell_exec('observe-agent status 2>&1');
    $finalTraceCount = 0;
    
    if (preg_match('/Traces Stats.*?ReceiverAcceptedCount:\s*(\d+)/s', $finalStats, $finalMatches)) {
        $finalTraceCount = (int)$finalMatches[1];
    }
    
    echo "\n========================================\n";
    echo "Test Results\n";
    echo "نتایج تست\n";
    echo "========================================\n";
    echo "Booking Created: ✓\n";
    echo "رزرو ایجاد شد: ✓\n";
    echo "  Booking ID: {$booking->id}\n";
    echo "  Reference: {$booking->booking_reference}\n";
    echo "\n";
    echo "Observe Agent Traces:\n";
    echo "traceهای Observe Agent:\n";
    echo "  Before: {$beforeTraceCount}\n";
    echo "  After: {$finalTraceCount}\n";
    echo "  Difference: " . ($finalTraceCount - $beforeTraceCount) . "\n";
    echo "\n";
    
    if ($finalTraceCount > $beforeTraceCount) {
        echo "✅ SUCCESS: Traces are being sent to Observe Agent!\n";
        echo "✅ موفقیت: traceها به Observe Agent ارسال می‌شوند!\n";
        echo "\n";
        echo "Next steps:\n";
        echo "مراحل بعدی:\n";
        echo "1. Check Observe dashboard for traces\n";
        echo "   داشبورد Observe را برای traceها بررسی کنید\n";
        echo "2. Filter by service: hooshex\n";
        echo "   فیلتر بر اساس سرویس: hooshex\n";
        echo "3. Look for operation: beauty.booking.create\n";
        echo "   به دنبال عملیات: beauty.booking.create\n";
    } else {
        echo "⚠️  Note: Traces may be batched. Check again in a few seconds.\n";
        echo "⚠️  توجه: traceها ممکن است batch شده باشند. چند ثانیه دیگر دوباره بررسی کنید.\n";
        echo "   Or check Observe dashboard directly.\n";
        echo "   یا مستقیماً داشبورد Observe را بررسی کنید.\n";
    }
    
    echo "\n";
    echo "Full Observe Agent Stats:\n";
    echo "آمار کامل Observe Agent:\n";
    echo shell_exec('observe-agent status 2>&1 | grep -A 6 "Traces Stats"');
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "❌ خطا: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

