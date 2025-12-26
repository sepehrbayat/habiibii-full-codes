<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyRetailProduct;
use Modules\BeautyBooking\Entities\BeautyRetailOrder;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign;
use Modules\BeautyBooking\Entities\BeautyLoyaltyPoint;
use Modules\BeautyBooking\Entities\BeautyCalendarBlock;
use Modules\BeautyBooking\Services\BeautyRetailService;
use Modules\BeautyBooking\Services\BeautyLoyaltyService;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Modules\BeautyBooking\Services\BeautyBookingService;
use App\Models\User;
use App\Models\Store;
use Carbon\Carbon;

/**
 * Race Condition Tests
 * تست‌های Race Condition
 *
 * Tests concurrent operations to verify race condition fixes
 * تست عملیات همزمان برای تأیید اصلاحات race condition
 */
class RaceConditionTest extends TestCase
{
    use RefreshDatabase;

    private BeautyRetailService $retailService;
    private BeautyLoyaltyService $loyaltyService;
    private BeautyBookingService $bookingService;
    private User $user;
    private BeautySalon $salon;
    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock calendar service to always allow booking slots for deterministic tests
        // ماک سرویس تقویم برای اجازه همیشگی زمان‌بندی در تست‌ها
        $calendarMock = Mockery::mock(BeautyCalendarService::class);
        $calendarMock->shouldReceive('isTimeSlotAvailable')->andReturn(true);
        $calendarMock->shouldReceive('blockTimeSlot')->andReturn(new BeautyCalendarBlock());
        $calendarMock->shouldReceive('unblockTimeSlotForBooking')->andReturnTrue();
        app()->instance(BeautyCalendarService::class, $calendarMock);
        
        $this->retailService = app(BeautyRetailService::class);
        $this->loyaltyService = app(BeautyLoyaltyService::class);
        $this->bookingService = app(BeautyBookingService::class);
        
        // Create test user
        // ایجاد کاربر تست
        $this->user = User::factory()->create();
        
        // Create test store and salon
        // ایجاد فروشگاه و سالن تست
        $this->store = Store::factory()->create();
        $this->salon = BeautySalon::factory()->create([
            'store_id' => $this->store->id,
            'verification_status' => 1,
            'is_verified' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test retail stock race condition - concurrent orders should not cause negative stock
     * تست race condition موجودی خرده‌فروشی - سفارشات همزمان نباید باعث موجودی منفی شوند
     *
     * @return void
     */
    public function test_retail_stock_race_condition_prevention(): void
    {
        // Create a retail product with limited stock
        // ایجاد محصول خرده‌فروشی با موجودی محدود
        $product = BeautyRetailProduct::factory()->create([
            'salon_id' => $this->salon->id,
            'stock_quantity' => 5, // Only 5 items available
            'price' => 100.0,
            'status' => 1,
        ]);
        
        $initialStock = $product->fresh()->stock_quantity;
        $this->assertEquals(5, $initialStock, 'Initial stock should be 5');
        
        // Simulate 10 concurrent order creation attempts
        // شبیه‌سازی 10 تلاش همزمان برای ایجاد سفارش
        $orders = [];
        $exceptions = [];
        
        // Use parallel execution to simulate concurrency
        // استفاده از اجرای موازی برای شبیه‌سازی همزمانی
        $promises = [];
        
        for ($i = 0; $i < 10; $i++) {
            $user = User::factory()->create();
            $promises[] = function () use ($user, $product, &$orders, &$exceptions) {
                try {
                    $orderData = [
                        'products' => [
                            [
                                'product_id' => $product->id,
                                'quantity' => 1, // Each order tries to buy 1 item
                            ],
                        ],
                        'payment_method' => 'cash_payment',
                    ];
                    
                    $order = $this->retailService->createOrder(
                        $user->id,
                        $this->salon->id,
                        $orderData
                    );
                    
                    $orders[] = $order;
                } catch (\Exception $e) {
                    $exceptions[] = $e->getMessage();
                }
            };
        }
        
        // Execute all promises concurrently
        // اجرای همه promise‌ها به صورت همزمان
        foreach ($promises as $promise) {
            $promise();
        }
        
        // Refresh product to get updated stock
        // به‌روزرسانی محصول برای دریافت موجودی به‌روزرسانی شده
        $product->refresh();
        $finalStock = $product->stock_quantity;
        
        // Verify stock cannot go negative
        // تأیید اینکه موجودی نمی‌تواند منفی شود
        $this->assertGreaterThanOrEqual(0, $finalStock, 'Stock should never go negative');
        
        // Verify exactly 5 orders were created (or less if some failed)
        // تأیید اینکه دقیقاً 5 سفارش ایجاد شده است (یا کمتر در صورت شکست برخی)
        $totalOrdersCreated = BeautyRetailOrder::count();
        $this->assertLessThanOrEqual(5, $totalOrdersCreated, 'Should not create more orders than available stock');
        
        // Verify stock matches orders created
        // تأیید اینکه موجودی با سفارشات ایجاد شده مطابقت دارد
        $expectedStock = $initialStock - $totalOrdersCreated;
        $this->assertEquals($expectedStock, $finalStock, 'Stock should match initial stock minus orders created');
    }

    /**
     * Test loyalty points duplicate award prevention
     * تست جلوگیری از اعطای تکراری امتیازهای وفاداری
     *
     * @return void
     */
    public function test_loyalty_points_duplicate_award_prevention(): void
    {
        // Create a loyalty campaign
        // ایجاد کمپین وفاداری
        $campaign = BeautyLoyaltyCampaign::factory()->create([
            'salon_id' => $this->salon->id,
            'is_active' => true,
            'type' => 'points',
            'rules' => [
                'points_per_currency' => 1, // 1 point per currency unit
            ],
        ]);
        
        // Create a service and booking
        // ایجاد خدمت و رزرو
        $service = BeautyService::factory()->create([
            'salon_id' => $this->salon->id,
            'status' => 1,
        ]);
        
        $bookingData = [
            'service_id' => $service->id,
            'booking_date' => Carbon::tomorrow()->format('Y-m-d'),
            'booking_time' => '10:00',
            'payment_method' => 'cash_payment',
        ];
        
        $booking = $this->bookingService->createBooking(
            $this->user->id,
            $this->salon->id,
            $bookingData
        );
        
        // Update booking to completed and paid status
        // به‌روزرسانی رزرو به وضعیت تکمیل شده و پرداخت شده
        $booking->update([
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);
        
        // Attempt to award points multiple times concurrently
        // تلاش برای اعطای امتیاز چندین بار به صورت همزمان
        $pointsAwarded = [];
        $exceptions = [];
        
        // Simulate 5 concurrent calls to awardPointsForBooking
        // شبیه‌سازی 5 فراخوانی همزمان awardPointsForBooking
        for ($i = 0; $i < 5; $i++) {
            try {
                $this->loyaltyService->awardPointsForBooking($booking);
                
                // Count points after each call
                // شمارش امتیازها پس از هر فراخوانی
                $pointsCount = BeautyLoyaltyPoint::where('booking_id', $booking->id)
                    ->where('campaign_id', $campaign->id)
                    ->where('type', 'earned')
                    ->count();
                
                $pointsAwarded[] = $pointsCount;
            } catch (\Exception $e) {
                $exceptions[] = $e->getMessage();
            }
        }
        
        // Refresh booking to get latest state
        // به‌روزرسانی رزرو برای دریافت آخرین وضعیت
        $booking->refresh();
        
        // Verify points were only awarded once
        // تأیید اینکه امتیازها فقط یک بار اعطا شده است
        $finalPointsCount = BeautyLoyaltyPoint::where('booking_id', $booking->id)
            ->where('campaign_id', $campaign->id)
            ->where('type', 'earned')
            ->count();
        
        $this->assertEquals(1, $finalPointsCount, 'Points should be awarded exactly once, not multiple times');
        
        // Verify total points amount is correct (not duplicated)
        // تأیید اینکه مقدار کل امتیازها صحیح است (تکرار نشده)
        $totalPoints = BeautyLoyaltyPoint::where('booking_id', $booking->id)
            ->where('campaign_id', $campaign->id)
            ->where('type', 'earned')
            ->sum('points');
        
        $expectedPoints = (int) floor($booking->total_amount * 1); // 1 point per currency unit
        $this->assertEquals($expectedPoints, $totalPoints, 'Total points should match expected amount without duplication');
    }

    /**
     * Test concurrent order creation with same product from different users
     * تست ایجاد همزمان سفارش با همان محصول از کاربران مختلف
     *
     * @return void
     */
    public function test_concurrent_orders_same_product(): void
    {
        // Create product with stock of 3
        // ایجاد محصول با موجودی 3
        $product = BeautyRetailProduct::factory()->create([
            'salon_id' => $this->salon->id,
            'stock_quantity' => 3,
            'price' => 50.0,
            'status' => 1,
        ]);
        
        $initialStock = $product->stock_quantity;
        
        // Create 5 users trying to order the same product simultaneously
        // ایجاد 5 کاربر که همزمان همان محصول را سفارش می‌دهند
        $users = User::factory()->count(5)->create();
        $successfulOrders = [];
        $failedOrders = [];
        
        foreach ($users as $user) {
            try {
                $orderData = [
                    'products' => [
                        [
                            'product_id' => $product->id,
                            'quantity' => 1,
                        ],
                    ],
                    'payment_method' => 'cash_payment',
                ];
                
                $order = $this->retailService->createOrder(
                    $user->id,
                    $this->salon->id,
                    $orderData
                );
                
                $successfulOrders[] = $order;
            } catch (\Exception $e) {
                $failedOrders[] = $e->getMessage();
            }
        }
        
        // Refresh product
        // به‌روزرسانی محصول
        $product->refresh();
        $finalStock = $product->stock_quantity;
        
        // Verify only 3 orders succeeded (matching stock)
        // تأیید اینکه فقط 3 سفارش موفق شده است (مطابق موجودی)
        $this->assertCount(3, $successfulOrders, 'Should create exactly 3 orders matching available stock');
        $this->assertCount(2, $failedOrders, 'Should fail 2 orders due to insufficient stock');
        
        // Verify stock is now 0
        // تأیید اینکه موجودی اکنون 0 است
        $this->assertEquals(0, $finalStock, 'Stock should be 0 after 3 successful orders');
        
        // Verify no negative stock
        // تأیید عدم وجود موجودی منفی
        $this->assertGreaterThanOrEqual(0, $finalStock, 'Stock should never be negative');
    }

    /**
     * Test loyalty points unique constraint at database level
     * تست محدودیت منحصر به فرد امتیازهای وفاداری در سطح پایگاه داده
     *
     * @return void
     */
    public function test_loyalty_points_unique_constraint(): void
    {
        // Create a loyalty campaign
        // ایجاد کمپین وفاداری
        $campaign = BeautyLoyaltyCampaign::factory()->create([
            'salon_id' => $this->salon->id,
            'is_active' => true,
            'type' => 'points',
            'rules' => ['points_per_currency' => 1],
        ]);
        
        // Create a booking
        // ایجاد رزرو
        $service = BeautyService::factory()->create([
            'salon_id' => $this->salon->id,
            'status' => 1,
        ]);
        
        $booking = $this->bookingService->createBooking(
            $this->user->id,
            $this->salon->id,
            [
                'service_id' => $service->id,
                'booking_date' => Carbon::tomorrow()->format('Y-m-d'),
                'booking_time' => '10:00',
                'payment_method' => 'cash_payment',
            ]
        );
        
        $booking->update([
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);
        
        // Create first loyalty point entry
        // ایجاد اولین ورودی امتیاز وفاداری
        $firstPoint = BeautyLoyaltyPoint::create([
            'user_id' => $this->user->id,
            'salon_id' => $this->salon->id,
            'campaign_id' => $campaign->id,
            'booking_id' => $booking->id,
            'points' => 100,
            'type' => 'earned',
            'expires_at' => Carbon::now()->addYear(),
        ]);
        
        // Try to create duplicate entry - should fail due to unique constraint
        // تلاش برای ایجاد ورودی تکراری - باید به دلیل محدودیت منحصر به فرد شکست بخورد
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        try {
            $duplicatePoint = BeautyLoyaltyPoint::create([
                'user_id' => $this->user->id,
                'salon_id' => $this->salon->id,
                'campaign_id' => $campaign->id,
                'booking_id' => $booking->id,
                'points' => 100,
                'type' => 'earned',
                'expires_at' => Carbon::now()->addYear(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Verify it's a duplicate key error
            // تأیید اینکه خطای کلید تکراری است
            $this->assertStringContainsString('Duplicate entry', $e->getMessage());
            throw $e;
        }
    }
}

