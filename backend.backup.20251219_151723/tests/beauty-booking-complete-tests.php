<?php

/**
 * Complete Beauty Booking Module Tests with Observe Agent
 * تست‌های کامل ماژول Beauty Booking با Observe Agent
 * 
 * Comprehensive test suite for all Beauty Booking features
 * مجموعه تست جامع برای تمام ویژگی‌های Beauty Booking
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\BeautyBooking\Services\BeautyBookingService;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Modules\BeautyBooking\Services\BeautyRankingService;
use Modules\BeautyBooking\Services\BeautyCrossSellingService;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyServiceCategory;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyReview;
use Modules\BeautyBooking\Entities\BeautyPackage;
use Modules\BeautyBooking\Entities\BeautyGiftCard;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "Beauty Booking Complete Test Suite\n";
echo "مجموعه تست کامل Beauty Booking\n";
echo "With Observe Agent Monitoring\n";
echo "با نظارت Observe Agent\n";
echo "========================================\n\n";

// Helper function to get trace count
function getTraceCount(): int
{
    $stats = shell_exec('observe-agent status 2>&1');
    if (preg_match('/Traces Stats.*?ReceiverAcceptedCount:\s*(\d+)/s', $stats, $matches)) {
        return (int)$matches[1];
    }
    return 0;
}

// Helper function to record test
function recordTest(string $name, bool $success, string $message = '', array $data = []): void
{
    global $beforeTraceCount;
    $status = $success ? '✓' : '✗';
    echo "{$status} {$name}\n";
    if ($message) {
        echo "  {$message}\n";
    }
    if (!empty($data)) {
        foreach ($data as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
    }
    echo "\n";
    sleep(1); // Wait for spans
    $beforeTraceCount = getTraceCount();
}

$results = [];
$testData = [];
$beforeTraceCount = getTraceCount();

echo "Initial trace count: {$beforeTraceCount}\n";
echo "تعداد trace اولیه: {$beforeTraceCount}\n\n";

// Test 1: Salon Search
try {
    $salons = BeautySalon::where('is_verified', true)
        ->whereHas('store', function($q) {
            $q->where('status', 1)->where('active', 1);
        })
        ->limit(5)
        ->get();
    recordTest('Salon Search', $salons->count() > 0, "Found {$salons->count()} salons", ['count' => $salons->count()]);
    if ($salons->count() > 0) {
        $testData['salon_id'] = $salons->first()->id;
    }
} catch (\Exception $e) {
    recordTest('Salon Search', false, $e->getMessage());
}

// Test 2: Get Salon Details
try {
    if (!isset($testData['salon_id'])) {
        recordTest('Get Salon Details', false, 'No salon available');
    } else {
        $salon = BeautySalon::with(['store', 'services', 'staff'])->find($testData['salon_id']);
        recordTest('Get Salon Details', $salon !== null, "Salon ID: {$testData['salon_id']}", ['services' => $salon->services->count()]);
    }
} catch (\Exception $e) {
    recordTest('Get Salon Details', false, $e->getMessage());
}

// Test 3: Service Categories
try {
    $categories = BeautyServiceCategory::where('status', 1)->get();
    recordTest('Get Service Categories', $categories->count() > 0, "Found {$categories->count()} categories");
} catch (\Exception $e) {
    recordTest('Get Service Categories', false, $e->getMessage());
}

// Test 4: Check Availability
try {
    if (!isset($testData['salon_id'])) {
        recordTest('Check Availability', false, 'No salon available');
    } else {
        $calendarService = app(BeautyCalendarService::class);
        $service = BeautyService::where('salon_id', $testData['salon_id'])->first();
        if ($service) {
            $tomorrow = now()->addDay()->format('Y-m-d');
            $slots = $calendarService->getAvailableTimeSlots($testData['salon_id'], null, $tomorrow, $service->duration_minutes);
            recordTest('Check Availability', true, "Found " . count($slots) . " slots", ['date' => $tomorrow]);
        } else {
            recordTest('Check Availability', false, 'No service found');
        }
    }
} catch (\Exception $e) {
    recordTest('Check Availability', false, $e->getMessage());
}

// Test 5: Create Booking
try {
    if (!isset($testData['salon_id'])) {
        recordTest('Create Booking', false, 'No salon available');
    } else {
        $user = User::first();
        $salon = BeautySalon::find($testData['salon_id']);
        $service = BeautyService::where('salon_id', $salon->id)->first();
        
        if (!$user || !$service) {
            recordTest('Create Booking', false, 'Missing user or service');
        } else {
            $bookingService = app(BeautyBookingService::class);
            $tomorrow = now()->addDay()->format('Y-m-d');
            $timeSlots = ['09:00', '10:00', '11:00', '14:00', '15:00'];
            $booking = null;
            
            foreach ($timeSlots as $time) {
                try {
                    $booking = $bookingService->createBooking($user->id, $salon->id, [
                        'service_id' => $service->id,
                        'booking_date' => $tomorrow,
                        'booking_time' => $time,
                        'payment_method' => 'cash_payment',
                    ]);
                    break;
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            if ($booking) {
                $testData['booking_id'] = $booking->id;
                recordTest('Create Booking', true, "Created: {$booking->booking_reference}", [
                    'id' => $booking->id,
                    'status' => $booking->status,
                    'amount' => $booking->total_amount
                ]);
            } else {
                recordTest('Create Booking', false, 'No available time slots');
            }
        }
    }
} catch (\Exception $e) {
    recordTest('Create Booking', false, $e->getMessage());
}

// Test 6: Get Booking Details
try {
    if (!isset($testData['booking_id'])) {
        recordTest('Get Booking Details', false, 'No booking available');
    } else {
        $booking = BeautyBooking::with(['salon', 'service', 'user'])->find($testData['booking_id']);
        recordTest('Get Booking Details', $booking !== null, "Reference: {$booking->booking_reference}");
    }
} catch (\Exception $e) {
    recordTest('Get Booking Details', false, $e->getMessage());
}

// Test 7: List User Bookings
try {
    $user = User::first();
    if ($user) {
        $bookings = BeautyBooking::where('user_id', $user->id)->limit(10)->get();
        recordTest('List User Bookings', true, "Found {$bookings->count()} bookings");
    } else {
        recordTest('List User Bookings', false, 'No user found');
    }
} catch (\Exception $e) {
    recordTest('List User Bookings', false, $e->getMessage());
}

// Test 8: Create Review
try {
    if (!isset($testData['booking_id'])) {
        recordTest('Create Review', false, 'No booking available');
    } else {
        $user = User::first();
        $booking = BeautyBooking::find($testData['booking_id']);
        
        $existing = BeautyReview::where('booking_id', $booking->id)->first();
        if ($existing) {
            recordTest('Create Review', true, "Review exists: ID {$existing->id}");
        } else {
            $review = BeautyReview::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'salon_id' => $booking->salon_id,
                'service_id' => $booking->service_id,
                'rating' => 5,
                'comment' => 'Test review for Observe Agent',
                'status' => 'pending',
            ]);
            recordTest('Create Review', true, "Created: ID {$review->id}", ['rating' => $review->rating]);
        }
    }
} catch (\Exception $e) {
    recordTest('Create Review', false, $e->getMessage());
}

// Test 9: Get Salon Reviews
try {
    if (!isset($testData['salon_id'])) {
        recordTest('Get Salon Reviews', false, 'No salon available');
    } else {
        $reviews = BeautyReview::where('salon_id', $testData['salon_id'])->get();
        recordTest('Get Salon Reviews', true, "Found {$reviews->count()} reviews");
    }
} catch (\Exception $e) {
    recordTest('Get Salon Reviews', false, $e->getMessage());
}

// Test 10: Service Suggestions
try {
    if (!isset($testData['salon_id'])) {
        recordTest('Service Suggestions', false, 'No salon available');
    } else {
        $service = BeautyService::where('salon_id', $testData['salon_id'])->first();
        if ($service) {
            $crossSellingService = app(BeautyCrossSellingService::class);
            $user = User::first();
            $suggestions = $crossSellingService->getSuggestedServices($service->id, $user ? $user->id : null, $testData['salon_id']);
            recordTest('Service Suggestions', true, "Found " . count($suggestions) . " suggestions");
        } else {
            recordTest('Service Suggestions', false, 'No service found');
        }
    }
} catch (\Exception $e) {
    recordTest('Service Suggestions', false, $e->getMessage());
}

// Test 11: Popular Salons
try {
    $salons = BeautySalon::where('is_verified', true)
        ->whereHas('store', function($q) {
            $q->where('status', 1)->where('active', 1);
        })
        ->where('total_bookings', '>', 0)
        ->orderByDesc('total_bookings')
        ->limit(5)
        ->get();
    recordTest('Get Popular Salons', true, "Found " . count($salons) . " popular salons");
} catch (\Exception $e) {
    recordTest('Get Popular Salons', false, $e->getMessage());
}

// Test 12: Top Rated Salons
try {
    $salons = BeautySalon::where('is_verified', true)
        ->whereHas('store', function($q) {
            $q->where('status', 1)->where('active', 1);
        })
        ->where('avg_rating', '>', 0)
        ->orderByDesc('avg_rating')
        ->limit(5)
        ->get();
    recordTest('Get Top Rated Salons', true, "Found " . count($salons) . " top rated salons");
} catch (\Exception $e) {
    recordTest('Get Top Rated Salons', false, $e->getMessage());
}

// Test 13: Ranking Service
try {
    if (!isset($testData['salon_id'])) {
        recordTest('Calculate Ranking', false, 'No salon available');
    } else {
        $rankingService = app(BeautyRankingService::class);
        $salon = BeautySalon::find($testData['salon_id']);
        $score = $rankingService->calculateRankingScore($salon);
        recordTest('Calculate Ranking', true, "Score: " . round($score, 2));
    }
} catch (\Exception $e) {
    recordTest('Calculate Ranking', false, $e->getMessage());
}

// Test 14: Get Ranked Salons
try {
    $rankingService = app(BeautyRankingService::class);
    $salons = $rankingService->getRankedSalons(null, null, null, []);
    recordTest('Get Ranked Salons', $salons->count() > 0, "Found " . $salons->count() . " ranked salons");
} catch (\Exception $e) {
    recordTest('Get Ranked Salons', false, $e->getMessage());
}

// Test 15: Get Packages
try {
    if (!isset($testData['salon_id'])) {
        recordTest('Get Packages', false, 'No salon available');
    } else {
        $packages = BeautyPackage::where('salon_id', $testData['salon_id'])->get();
        recordTest('Get Packages', true, "Found " . count($packages) . " packages");
    }
} catch (\Exception $e) {
    recordTest('Get Packages', false, $e->getMessage());
}

// Test 16: Get Gift Cards
try {
    $user = User::first();
    if ($user) {
        // Gift cards use 'purchased_by' not 'user_id'
        // کارت‌های هدیه از 'purchased_by' استفاده می‌کنند نه 'user_id'
        $giftCards = BeautyGiftCard::where('purchased_by', $user->id)
            ->orWhere('redeemed_by', $user->id)
            ->get();
        recordTest('Get Gift Cards', true, "Found " . count($giftCards) . " gift cards");
    } else {
        recordTest('Get Gift Cards', false, 'No user found');
    }
} catch (\Exception $e) {
    recordTest('Get Gift Cards', false, $e->getMessage());
}

// Final wait
echo "Waiting for all spans to be sent...\n";
echo "در انتظار ارسال تمام spanها...\n";
sleep(10);

$finalTraceCount = getTraceCount();

echo "\n========================================\n";
echo "Test Summary\n";
echo "خلاصه تست\n";
echo "========================================\n";
echo "Initial Traces: {$beforeTraceCount}\n";
echo "Final Traces: {$finalTraceCount}\n";
echo "New Traces: " . ($finalTraceCount - $beforeTraceCount) . "\n";
echo "\n";
echo "Observe Agent Status:\n";
echo shell_exec('observe-agent status 2>&1 | grep -A 6 "Traces Stats"');
echo "\n";
echo "✅ Test Suite Complete!\n";
echo "✅ مجموعه تست کامل شد!\n";
echo "========================================\n";

