<?php

/**
 * Complete Beauty Booking Module Test Suite with Observe Agent
 * مجموعه تست کامل ماژول Beauty Booking با Observe Agent
 * 
 * Tests all features of the Beauty Booking module and monitors traces
 * تست تمام ویژگی‌های ماژول Beauty Booking و نظارت بر traceها
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\BeautyBooking\Services\BeautyBookingService;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Modules\BeautyBooking\Services\BeautyRankingService;
use Modules\BeautyBooking\Services\BeautyBadgeService;
use Modules\BeautyBooking\Services\BeautyLoyaltyService;
use Modules\BeautyBooking\Services\BeautyRetailService;
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

class BeautyBookingTestSuite
{
    private $results = [];
    private $beforeTraceCount = 0;
    private $testData = [];
    
    public function __construct()
    {
        echo "========================================\n";
        echo "Beauty Booking Complete Test Suite\n";
        echo "مجموعه تست کامل Beauty Booking\n";
        echo "========================================\n\n";
    }
    
    /**
     * Get Observe Agent trace count
     */
    private function getTraceCount(): int
    {
        $stats = shell_exec('observe-agent status 2>&1');
        if (preg_match('/Traces Stats.*?ReceiverAcceptedCount:\s*(\d+)/s', $stats, $matches)) {
            return (int)$matches[1];
        }
        return 0;
    }
    
    /**
     * Record test result
     */
    private function recordResult(string $testName, bool $success, string $message = '', array $data = []): void
    {
        $this->results[] = [
            'test' => $testName,
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'traces_before' => $this->beforeTraceCount,
            'traces_after' => $this->getTraceCount(),
        ];
        
        $status = $success ? '✓' : '✗';
        echo "{$status} {$testName}\n";
        if ($message) {
            echo "  {$message}\n";
        }
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                echo "  {$key}: {$value}\n";
            }
        }
        echo "\n";
        
        // Wait for spans to be sent
        sleep(2);
        $this->beforeTraceCount = $this->getTraceCount();
    }
    
    /**
     * Test 1: Salon Search
     */
    public function testSalonSearch(): void
    {
        try {
            $salons = BeautySalon::where('is_verified', true)
                ->whereHas('store', function($q) {
                    $q->where('status', 1)->where('active', 1);
                })
                ->limit(5)
                ->get();
            
            $this->recordResult(
                'Salon Search',
                $salons->count() > 0,
                "Found {$salons->count()} verified salons",
                ['count' => $salons->count()]
            );
        } catch (\Exception $e) {
            $this->recordResult('Salon Search', false, $e->getMessage());
        }
    }
    
    /**
     * Test 2: Get Salon Details
     */
    public function testGetSalonDetails(): void
    {
        try {
            $salon = BeautySalon::where('is_verified', true)
                ->with(['store', 'services', 'staff'])
                ->first();
            
            if (!$salon) {
                $this->recordResult('Get Salon Details', false, 'No verified salon found');
                return;
            }
            
            $this->testData['salon_id'] = $salon->id;
            
            $this->recordResult(
                'Get Salon Details',
                true,
                "Retrieved salon: {$salon->id}",
                ['salon_id' => $salon->id, 'services_count' => $salon->services->count()]
            );
        } catch (\Exception $e) {
            $this->recordResult('Get Salon Details', false, $e->getMessage());
        }
    }
    
    /**
     * Test 3: Get Service Categories
     */
    public function testGetServiceCategories(): void
    {
        try {
            $categories = BeautyServiceCategory::where('status', 1)->get();
            
            $this->recordResult(
                'Get Service Categories',
                $categories->count() > 0,
                "Found {$categories->count()} categories",
                ['count' => $categories->count()]
            );
        } catch (\Exception $e) {
            $this->recordResult('Get Service Categories', false, $e->getMessage());
        }
    }
    
    /**
     * Test 4: Check Availability
     */
    public function testCheckAvailability(): void
    {
        try {
            if (!isset($this->testData['salon_id'])) {
                $this->recordResult('Check Availability', false, 'Salon ID not set');
                return;
            }
            
            $salon = BeautySalon::find($this->testData['salon_id']);
            $service = BeautyService::where('salon_id', $salon->id)->first();
            
            if (!$service) {
                $this->recordResult('Check Availability', false, 'No service found');
                return;
            }
            
            $calendarService = app(BeautyCalendarService::class);
            $tomorrow = now()->addDay()->format('Y-m-d');
            
            $slots = $calendarService->getAvailableTimeSlots(
                $salon->id,
                null,
                $tomorrow,
                $service->duration_minutes
            );
            
            $this->recordResult(
                'Check Availability',
                true,
                "Found " . count($slots) . " available slots",
                ['date' => $tomorrow, 'slots_count' => count($slots)]
            );
        } catch (\Exception $e) {
            $this->recordResult('Check Availability', false, $e->getMessage());
        }
    }
    
    /**
     * Test 5: Create Booking
     */
    public function testCreateBooking(): void
    {
        try {
            if (!isset($this->testData['salon_id'])) {
                $this->recordResult('Create Booking', false, 'Salon ID not set');
                return;
            }
            
            $user = User::first();
            $salon = BeautySalon::find($this->testData['salon_id']);
            $service = BeautyService::where('salon_id', $salon->id)->first();
            
            if (!$user || !$service) {
                $this->recordResult('Create Booking', false, 'Missing user or service');
                return;
            }
            
            $bookingService = app(BeautyBookingService::class);
            $tomorrow = now()->addDay()->format('Y-m-d');
            
            // Try different time slots until we find an available one
            $timeSlots = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];
            $booking = null;
            $lastError = null;
            
            foreach ($timeSlots as $time) {
                try {
                    $booking = $bookingService->createBooking(
                        $user->id,
                        $salon->id,
                        [
                            'service_id' => $service->id,
                            'booking_date' => $tomorrow,
                            'booking_time' => $time,
                            'payment_method' => 'cash_payment',
                        ]
                    );
                    break; // Success, exit loop
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                    continue; // Try next time slot
                }
            }
            
            if (!$booking) {
                throw new \Exception($lastError ?? 'No available time slots');
            }
            
            $this->testData['booking_id'] = $booking->id;
            
            $this->recordResult(
                'Create Booking',
                true,
                "Booking created: {$booking->booking_reference}",
                [
                    'booking_id' => $booking->id,
                    'reference' => $booking->booking_reference,
                    'status' => $booking->status,
                    'amount' => $booking->total_amount,
                ]
            );
        } catch (\Exception $e) {
            $this->recordResult('Create Booking', false, $e->getMessage());
        }
    }
    
    /**
     * Test 6: Get Booking Details
     */
    public function testGetBookingDetails(): void
    {
        try {
            if (!isset($this->testData['booking_id'])) {
                $this->recordResult('Get Booking Details', false, 'Booking ID not set');
                return;
            }
            
            $booking = BeautyBooking::with(['salon', 'service', 'user', 'staff'])
                ->find($this->testData['booking_id']);
            
            $this->recordResult(
                'Get Booking Details',
                $booking !== null,
                $booking ? "Retrieved booking: {$booking->booking_reference}" : 'Booking not found',
                $booking ? ['reference' => $booking->booking_reference, 'status' => $booking->status] : []
            );
        } catch (\Exception $e) {
            $this->recordResult('Get Booking Details', false, $e->getMessage());
        }
    }
    
    /**
     * Test 7: List User Bookings
     */
    public function testListUserBookings(): void
    {
        try {
            $user = User::first();
            if (!$user) {
                $this->recordResult('List User Bookings', false, 'No user found');
                return;
            }
            
            $bookings = BeautyBooking::where('user_id', $user->id)
                ->with(['salon', 'service'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            $this->recordResult(
                'List User Bookings',
                true,
                "Found {$bookings->count()} bookings for user",
                ['user_id' => $user->id, 'count' => $bookings->count()]
            );
        } catch (\Exception $e) {
            $this->recordResult('List User Bookings', false, $e->getMessage());
        }
    }
    
    /**
     * Test 8: Cancel Booking
     */
    public function testCancelBooking(): void
    {
        try {
            if (!isset($this->testData['booking_id'])) {
                $this->recordResult('Cancel Booking', false, 'Booking ID not set');
                return;
            }
            
            $booking = BeautyBooking::find($this->testData['booking_id']);
            if (!$booking || $booking->status === 'cancelled') {
                $this->recordResult('Cancel Booking', false, 'Booking not found or already cancelled');
                return;
            }
            
            $bookingService = app(BeautyBookingService::class);
            $bookingService->cancelBooking($booking, 'Test cancellation');
            
            $booking->refresh();
            
            $this->recordResult(
                'Cancel Booking',
                $booking->status === 'cancelled',
                "Booking cancelled: {$booking->booking_reference}",
                ['status' => $booking->status]
            );
        } catch (\Exception $e) {
            $this->recordResult('Cancel Booking', false, $e->getMessage());
        }
    }
    
    /**
     * Test 9: Create Review
     */
    public function testCreateReview(): void
    {
        try {
            $user = User::first();
            if (!isset($this->testData['booking_id'])) {
                // Create a new booking for review
                $salon = BeautySalon::where('is_verified', true)->first();
                $service = BeautyService::where('salon_id', $salon->id)->first();
                
                if (!$salon || !$service) {
                    $this->recordResult('Create Review', false, 'Missing salon or service');
                    return;
                }
                
                $bookingService = app(BeautyBookingService::class);
                $booking = $bookingService->createBooking(
                    $user->id,
                    $salon->id,
                    [
                        'service_id' => $service->id,
                        'booking_date' => now()->addDays(3)->format('Y-m-d'),
                        'booking_time' => '15:00',
                        'payment_method' => 'cash_payment',
                    ]
                );
                $this->testData['booking_id'] = $booking->id;
            }
            
            $booking = BeautyBooking::find($this->testData['booking_id']);
            
            // Check if review already exists
            $existingReview = BeautyReview::where('booking_id', $booking->id)->first();
            if ($existingReview) {
                $this->recordResult('Create Review', true, 'Review already exists', ['review_id' => $existingReview->id]);
                return;
            }
            
            $review = BeautyReview::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'salon_id' => $booking->salon_id,
                'service_id' => $booking->service_id,
                'rating' => 5,
                'comment' => 'Great service! Test review for Observe Agent.',
                'status' => 'pending',
            ]);
            
            $this->recordResult(
                'Create Review',
                true,
                "Review created: ID {$review->id}",
                ['review_id' => $review->id, 'rating' => $review->rating]
            );
        } catch (\Exception $e) {
            $this->recordResult('Create Review', false, $e->getMessage());
        }
    }
    
    /**
     * Test 10: Get Salon Reviews
     */
    public function testGetSalonReviews(): void
    {
        try {
            if (!isset($this->testData['salon_id'])) {
                $this->recordResult('Get Salon Reviews', false, 'Salon ID not set');
                return;
            }
            
            $reviews = BeautyReview::where('salon_id', $this->testData['salon_id'])
                ->where('status', 'approved')
                ->with(['user'])
                ->limit(10)
                ->get();
            
            $this->recordResult(
                'Get Salon Reviews',
                true,
                "Found {$reviews->count()} approved reviews",
                ['salon_id' => $this->testData['salon_id'], 'count' => $reviews->count()]
            );
        } catch (\Exception $e) {
            $this->recordResult('Get Salon Reviews', false, $e->getMessage());
        }
    }
    
    /**
     * Test 11: Service Suggestions (Cross-Selling)
     */
    public function testServiceSuggestions(): void
    {
        try {
            if (!isset($this->testData['salon_id'])) {
                $this->recordResult('Service Suggestions', false, 'Salon ID not set');
                return;
            }
            
            $service = BeautyService::where('salon_id', $this->testData['salon_id'])->first();
            if (!$service) {
                $this->recordResult('Service Suggestions', false, 'No service found');
                return;
            }
            
            $crossSellingService = app(BeautyCrossSellingService::class);
            $user = User::first();
            
            $suggestions = $crossSellingService->getSuggestedServices(
                $service->id,
                $user ? $user->id : null,
                $this->testData['salon_id']
            );
            
            $this->recordResult(
                'Service Suggestions',
                true,
                "Found " . count($suggestions) . " suggestions",
                ['service_id' => $service->id, 'suggestions_count' => count($suggestions)]
            );
        } catch (\Exception $e) {
            $this->recordResult('Service Suggestions', false, $e->getMessage());
        }
    }
    
    /**
     * Test 12: Get Popular Salons
     */
    public function testGetPopularSalons(): void
    {
        try {
            $salons = BeautySalon::where('is_verified', true)
                ->whereHas('store', function($q) {
                    $q->where('status', 1)->where('active', 1);
                })
                ->where('total_bookings', '>', 0)
                ->orderByDesc('total_bookings')
                ->limit(5)
                ->get();
            
            $this->recordResult(
                'Get Popular Salons',
                $salons->count() > 0,
                "Found " . count($salons) . " popular salons",
                ['count' => count($salons)]
            );
        } catch (\Exception $e) {
            $this->recordResult('Get Popular Salons', false, $e->getMessage());
        }
    }
    
    /**
     * Test 13: Get Top Rated Salons
     */
    public function testGetTopRatedSalons(): void
    {
        try {
            $salons = BeautySalon::where('is_verified', true)
                ->whereHas('store', function($q) {
                    $q->where('status', 1)->where('active', 1);
                })
                ->where('avg_rating', '>', 0)
                ->orderByDesc('avg_rating')
                ->limit(5)
                ->get();
            
            $this->recordResult(
                'Get Top Rated Salons',
                $salons->count() > 0,
                "Found " . count($salons) . " top rated salons",
                ['count' => count($salons)]
            );
        } catch (\Exception $e) {
            $this->recordResult('Get Top Rated Salons', false, $e->getMessage());
        }
    }
    
    /**
     * Test 14: Get Monthly Top Rated
     */
    public function testGetMonthlyTopRated(): void
    {
        try {
            $salons = BeautySalon::where('is_verified', true)
                ->whereHas('store', function($q) {
                    $q->where('status', 1)->where('active', 1);
                })
                ->where('avg_rating', '>', 0)
                ->orderByDesc('avg_rating')
                ->limit(5)
                ->get();
            
            $this->recordResult(
                'Get Monthly Top Rated',
                $salons->count() > 0,
                "Found " . count($salons) . " monthly top rated salons",
                ['count' => count($salons)]
            );
        } catch (\Exception $e) {
            $this->recordResult('Get Monthly Top Rated', false, $e->getMessage());
        }
    }
    
    /**
     * Test 15: Get Trending Clinics
     */
    public function testGetTrendingClinics(): void
    {
        try {
            $salons = BeautySalon::where('is_verified', true)
                ->where('business_type', 'clinic')
                ->whereHas('store', function($q) {
                    $q->where('status', 1)->where('active', 1);
                })
                ->orderByDesc('total_bookings')
                ->limit(5)
                ->get();
            
            $this->recordResult(
                'Get Trending Clinics',
                true,
                "Found " . count($salons) . " trending clinics",
                ['count' => count($salons)]
            );
        } catch (\Exception $e) {
            $this->recordResult('Get Trending Clinics', false, $e->getMessage());
        }
    }
    
    /**
     * Test 16: Get Loyalty Points
     */
    public function testGetLoyaltyPoints(): void
    {
        try {
            $user = User::first();
            if (!$user) {
                $this->recordResult('Get Loyalty Points', false, 'No user found');
                return;
            }
            
            $loyaltyService = app(BeautyLoyaltyService::class);
            // Use getTotalPoints method instead of getUserPoints
            // استفاده از متد getTotalPoints به جای getUserPoints
            $points = $loyaltyService->getTotalPoints($user->id);
            
            $this->recordResult(
                'Get Loyalty Points',
                true,
                "User has {$points} loyalty points",
                ['user_id' => $user->id, 'points' => $points]
            );
        } catch (\Exception $e) {
            $this->recordResult('Get Loyalty Points', false, $e->getMessage());
        }
    }
    
    /**
     * Test 17: Get Loyalty Campaigns
     */
    public function testGetLoyaltyCampaigns(): void
    {
        try {
            // Get campaigns directly from model using active scope
            // دریافت کمپین‌ها مستقیماً از مدل با استفاده از scope فعال
            $campaigns = \Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign::active()
                ->get();
            
            $this->recordResult(
                'Get Loyalty Campaigns',
                true,
                "Found " . count($campaigns) . " active campaigns",
                ['count' => count($campaigns)]
            );
        } catch (\Exception $e) {
            $this->recordResult('Get Loyalty Campaigns', false, $e->getMessage());
        }
    }
    
    /**
     * Test 18: Get Retail Products
     */
    public function testGetRetailProducts(): void
    {
        try {
            if (!isset($this->testData['salon_id'])) {
                $this->recordResult('Get Retail Products', false, 'Salon ID not set');
                return;
            }
            
            // Get retail products directly from model
            // دریافت محصولات خرده‌فروشی مستقیماً از مدل
            $products = \Modules\BeautyBooking\Entities\BeautyRetailProduct::where('salon_id', $this->testData['salon_id'])
                ->where('status', 'active')
                ->get();
            
            $this->recordResult(
                'Get Retail Products',
                true,
                "Found " . count($products) . " retail products",
                ['salon_id' => $this->testData['salon_id'], 'count' => count($products)]
            );
        } catch (\Exception $e) {
            $this->recordResult('Get Retail Products', false, $e->getMessage());
        }
    }
    
    /**
     * Test 19: Get Packages
     */
    public function testGetPackages(): void
    {
        try {
            if (!isset($this->testData['salon_id'])) {
                $this->recordResult('Get Packages', false, 'Salon ID not set');
                return;
            }
            
            $packages = BeautyPackage::where('salon_id', $this->testData['salon_id'])
                ->where('status', 'active')
                ->get();
            
            $this->recordResult(
                'Get Packages',
                true,
                "Found " . count($packages) . " active packages",
                ['salon_id' => $this->testData['salon_id'], 'count' => count($packages)]
            );
        } catch (\Exception $e) {
            $this->recordResult('Get Packages', false, $e->getMessage());
        }
    }
    
    /**
     * Test 20: Get Gift Cards
     */
    public function testGetGiftCards(): void
    {
        try {
            $user = User::first();
            if (!$user) {
                $this->recordResult('Get Gift Cards', false, 'No user found');
                return;
            }
            
            // Gift cards use 'purchased_by' and 'redeemed_by', not 'user_id'
            // کارت‌های هدیه از 'purchased_by' و 'redeemed_by' استفاده می‌کنند، نه 'user_id'
            $giftCards = BeautyGiftCard::where('purchased_by', $user->id)
                ->orWhere('redeemed_by', $user->id)
                ->where('status', 'active')
                ->get();
            
            $this->recordResult(
                'Get Gift Cards',
                true,
                "Found " . count($giftCards) . " active gift cards",
                ['user_id' => $user->id, 'count' => count($giftCards)]
            );
        } catch (\Exception $e) {
            $this->recordResult('Get Gift Cards', false, $e->getMessage());
        }
    }
    
    /**
     * Run all tests
     */
    public function runAll(): void
    {
        echo "Initializing...\n";
        echo "راه‌اندازی...\n\n";
        
        // Check OpenTelemetry
        if (!config('opentelemetry.enabled', false)) {
            echo "⚠️  OpenTelemetry is not enabled!\n";
            echo "⚠️  OpenTelemetry غیرفعال است!\n";
        } else {
            echo "✓ OpenTelemetry: ENABLED\n";
            echo "✓ Endpoint: " . config('opentelemetry.endpoint') . "\n\n";
        }
        
        // Get initial trace count
        $this->beforeTraceCount = $this->getTraceCount();
        echo "Initial trace count: {$this->beforeTraceCount}\n";
        echo "تعداد trace اولیه: {$this->beforeTraceCount}\n\n";
        
        echo "Starting tests...\n";
        echo "شروع تست‌ها...\n\n";
        
        // Run all tests
        $this->testSalonSearch();
        $this->testGetSalonDetails();
        $this->testGetServiceCategories();
        $this->testCheckAvailability();
        $this->testCreateBooking();
        $this->testGetBookingDetails();
        $this->testListUserBookings();
        $this->testCancelBooking();
        $this->testCreateReview();
        $this->testGetSalonReviews();
        $this->testServiceSuggestions();
        $this->testGetPopularSalons();
        $this->testGetTopRatedSalons();
        $this->testGetMonthlyTopRated();
        $this->testGetTrendingClinics();
        $this->testGetLoyaltyPoints();
        $this->testGetLoyaltyCampaigns();
        $this->testGetRetailProducts();
        $this->testGetPackages();
        $this->testGetGiftCards();
        
        // Final wait for all spans
        echo "Waiting for all spans to be sent...\n";
        echo "در انتظار ارسال تمام spanها...\n";
        sleep(10);
        
        $finalTraceCount = $this->getTraceCount();
        
        // Print summary
        echo "\n========================================\n";
        echo "Test Summary\n";
        echo "خلاصه تست\n";
        echo "========================================\n\n";
        
        $passed = 0;
        $failed = 0;
        
        foreach ($this->results as $result) {
            if ($result['success']) {
                $passed++;
            } else {
                $failed++;
            }
        }
        
        echo "Total Tests: " . count($this->results) . "\n";
        echo "Passed: {$passed}\n";
        echo "Failed: {$failed}\n";
        echo "\n";
        echo "Traces:\n";
        echo "  Before: {$this->beforeTraceCount}\n";
        echo "  After: {$finalTraceCount}\n";
        echo "  Difference: " . ($finalTraceCount - $this->beforeTraceCount) . "\n";
        echo "\n";
        
        echo "Detailed Results:\n";
        echo "نتایج تفصیلی:\n";
        foreach ($this->results as $result) {
            $status = $result['success'] ? '✓' : '✗';
            echo "  {$status} {$result['test']}\n";
            if ($result['message']) {
                echo "    {$result['message']}\n";
            }
        }
        
        echo "\n========================================\n";
        echo "Test Suite Complete!\n";
        echo "مجموعه تست کامل شد!\n";
        echo "========================================\n";
    }
}

// Run the test suite
$suite = new BeautyBookingTestSuite();
$suite->runAll();

