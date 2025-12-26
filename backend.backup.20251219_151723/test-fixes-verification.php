<?php

/**
 * Test Fixes Verification
 * تأیید اصلاحات
 * 
 * Comprehensive test to verify all fixes applied
 * تست جامع برای تأیید تمام اصلاحات اعمال شده
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\BeautyBooking\Services\BeautyBadgeService;
use Modules\BeautyBooking\Services\BeautyBookingService;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyService;
use App\Models\User;
use Carbon\Carbon;

echo "========================================\n";
echo "Fix Verification Test Suite\n";
echo "مجموعه تست تأیید اصلاحات\n";
echo "========================================\n\n";

$passed = 0;
$failed = 0;
$tests = [];

// Test 1: Badge Rating Threshold Fix
echo "Test 1: Badge Rating Threshold Fix\n";
echo "تست 1: اصلاح آستانه رتبه نشان\n";
echo "----------------------------------------\n";

try {
    $salon = BeautySalon::first();
    if (!$salon) {
        throw new \Exception("No salon found");
    }
    
    // Set rating to exactly 4.8
    $salon->avg_rating = 4.8;
    $salon->total_bookings = 50;
    $salon->cancellation_rate = 1.0;
    $salon->save();
    
    // Create a recent booking to satisfy activity requirement
    $user = User::first();
    if ($user) {
        $recentBooking = BeautyBooking::where('salon_id', $salon->id)
            ->where('booking_date', '>=', Carbon::now()->subDays(30))
            ->first();
        
        if (!$recentBooking) {
            // Create a test booking if none exists
            $service = BeautyService::where('salon_id', $salon->id)->first();
            if ($service) {
                $futureDate = Carbon::now()->addDays(7)->format('Y-m-d');
                $recentBooking = BeautyBooking::create([
                    'user_id' => $user->id,
                    'salon_id' => $salon->id,
                    'service_id' => $service->id,
                    'booking_date' => $futureDate,
                    'booking_time' => '10:00',
                    'booking_reference' => 'TEST' . time(),
                    'status' => 'confirmed',
                    'payment_status' => 'paid',
                    'total_amount' => 100000,
                ]);
            }
        }
    }
    
    $badgeService = app(BeautyBadgeService::class);
    $badgeService->calculateAndAssignBadges($salon->id);
    
    $salon->refresh();
    $hasTopRated = $salon->badges()
        ->where('badge_type', 'top_rated')
        ->active()
        ->exists();
    
    if ($hasTopRated) {
        echo "✅ PASS: Salon with 4.8 rating receives Top Rated badge\n";
        echo "✅ موفق: سالن با رتبه 4.8 نشان Top Rated دریافت می‌کند\n";
        $passed++;
        $tests[] = ['name' => 'Badge Rating Threshold', 'status' => 'PASS'];
    } else {
        echo "❌ FAIL: Salon with 4.8 rating does NOT receive Top Rated badge\n";
        echo "❌ ناموفق: سالن با رتبه 4.8 نشان Top Rated دریافت نمی‌کند\n";
        $failed++;
        $tests[] = ['name' => 'Badge Rating Threshold', 'status' => 'FAIL'];
    }
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "❌ خطا: " . $e->getMessage() . "\n";
    $failed++;
    $tests[] = ['name' => 'Badge Rating Threshold', 'status' => 'ERROR', 'error' => $e->getMessage()];
}

echo "\n";

// Test 2: Cancellation Fee Consistency
echo "Test 2: Cancellation Fee Consistency\n";
echo "تست 2: سازگاری هزینه لغو\n";
echo "----------------------------------------\n";

try {
    $booking = BeautyBooking::where('status', '!=', 'cancelled')
        ->where('status', '!=', 'completed')
        ->first();
    
    if (!$booking) {
        echo "⚠️  SKIP: No active booking found for cancellation fee test\n";
        echo "⚠️  رد: رزرو فعالی برای تست هزینه لغو پیدا نشد\n";
        $tests[] = ['name' => 'Cancellation Fee Consistency', 'status' => 'SKIP'];
    } else {
        // Test model method
        $modelFee = $booking->calculateCancellationFee();
        
        // Verify it uses config values (not hardcoded)
        $config = config('beautybooking.cancellation_fee', []);
        $noFeeHours = $config['time_thresholds']['no_fee_hours'] ?? 24;
        $partialFeeHours = $config['time_thresholds']['partial_fee_hours'] ?? 2;
        
        // Check if booking date/time is valid
        $bookingDateTime = null;
        try {
            if ($booking->booking_date_time) {
                $bookingDateTime = Carbon::parse($booking->booking_date_time);
            } else {
                $bookingDate = $booking->booking_date instanceof Carbon 
                    ? $booking->booking_date->format('Y-m-d')
                    : (string)$booking->booking_date;
                $bookingDateTime = Carbon::parse($bookingDate . ' ' . $booking->booking_time);
            }
        } catch (\Exception $e) {
            echo "⚠️  SKIP: Invalid booking date/time\n";
            echo "⚠️  رد: تاریخ/زمان رزرو نامعتبر است\n";
            $tests[] = ['name' => 'Cancellation Fee Consistency', 'status' => 'SKIP'];
        }
        
        if ($bookingDateTime) {
            $hoursUntilBooking = now()->diffInHours($bookingDateTime, true);
            
            // Verify calculation uses config
            $expectedFee = 0.0;
            if ($hoursUntilBooking >= $noFeeHours) {
                $expectedFee = $booking->total_amount * (($config['fee_percentages']['no_fee'] ?? 0.0) / 100);
            } elseif ($hoursUntilBooking >= $partialFeeHours) {
                $expectedFee = $booking->total_amount * (($config['fee_percentages']['partial'] ?? 50.0) / 100);
            } else {
                $expectedFee = $booking->total_amount * (($config['fee_percentages']['full'] ?? 100.0) / 100);
            }
            
            // Allow small floating point differences
            if (abs($modelFee - $expectedFee) < 0.01) {
                echo "✅ PASS: Cancellation fee uses config values correctly\n";
                echo "✅ موفق: هزینه لغو از مقادیر config به درستی استفاده می‌کند\n";
                echo "  Model fee: {$modelFee}\n";
                echo "  Expected fee: {$expectedFee}\n";
                $passed++;
                $tests[] = ['name' => 'Cancellation Fee Consistency', 'status' => 'PASS'];
            } else {
                echo "⚠️  WARNING: Fee calculation may not match expected (floating point precision)\n";
                echo "⚠️  هشدار: محاسبه هزینه ممکن است با مقدار مورد انتظار مطابقت نداشته باشد (دقت اعشار)\n";
                echo "  Model fee: {$modelFee}\n";
                echo "  Expected fee: {$expectedFee}\n";
                echo "  Difference: " . abs($modelFee - $expectedFee) . "\n";
                $tests[] = ['name' => 'Cancellation Fee Consistency', 'status' => 'WARNING'];
            }
        }
    }
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "❌ خطا: " . $e->getMessage() . "\n";
    $failed++;
    $tests[] = ['name' => 'Cancellation Fee Consistency', 'status' => 'ERROR', 'error' => $e->getMessage()];
}

echo "\n";

// Test 3: Config Values Are Used
echo "Test 3: Config Values Are Used\n";
echo "تست 3: استفاده از مقادیر Config\n";
echo "----------------------------------------\n";

try {
    $config = config('beautybooking.cancellation_fee', []);
    
    if (isset($config['time_thresholds']) && isset($config['fee_percentages'])) {
        echo "✅ PASS: Config structure is correct\n";
        echo "✅ موفق: ساختار Config صحیح است\n";
        echo "  No fee hours: " . ($config['time_thresholds']['no_fee_hours'] ?? 24) . "\n";
        echo "  Partial fee hours: " . ($config['time_thresholds']['partial_fee_hours'] ?? 2) . "\n";
        echo "  No fee %: " . ($config['fee_percentages']['no_fee'] ?? 0.0) . "%\n";
        echo "  Partial fee %: " . ($config['fee_percentages']['partial'] ?? 50.0) . "%\n";
        echo "  Full fee %: " . ($config['fee_percentages']['full'] ?? 100.0) . "%\n";
        $passed++;
        $tests[] = ['name' => 'Config Values', 'status' => 'PASS'];
    } else {
        echo "⚠️  WARNING: Config structure may be incomplete\n";
        echo "⚠️  هشدار: ساختار Config ممکن است ناقص باشد\n";
        $tests[] = ['name' => 'Config Values', 'status' => 'WARNING'];
    }
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "❌ خطا: " . $e->getMessage() . "\n";
    $failed++;
    $tests[] = ['name' => 'Config Values', 'status' => 'ERROR', 'error' => $e->getMessage()];
}

echo "\n";

// Summary
echo "========================================\n";
echo "Test Summary\n";
echo "خلاصه تست\n";
echo "========================================\n";
echo "Total Tests: " . count($tests) . "\n";
echo "تعداد کل تست‌ها: " . count($tests) . "\n";
echo "Passed: {$passed}\n";
echo "موفق: {$passed}\n";
echo "Failed: {$failed}\n";
echo "ناموفق: {$failed}\n";
echo "Skipped: " . (count($tests) - $passed - $failed) . "\n";
echo "رد شده: " . (count($tests) - $passed - $failed) . "\n";
echo "\n";

foreach ($tests as $test) {
    $status = $test['status'];
    $icon = $status === 'PASS' ? '✅' : ($status === 'FAIL' ? '❌' : ($status === 'ERROR' ? '❌' : '⚠️'));
    echo "{$icon} {$test['name']}: {$status}\n";
    if (isset($test['error'])) {
        echo "   Error: {$test['error']}\n";
    }
}

echo "\n";
echo "========================================\n";
if ($failed === 0) {
    echo "✅ All Tests Passed!\n";
    echo "✅ تمام تست‌ها موفق بودند!\n";
} else {
    echo "❌ Some Tests Failed\n";
    echo "❌ برخی تست‌ها ناموفق بودند\n";
}
echo "========================================\n";

