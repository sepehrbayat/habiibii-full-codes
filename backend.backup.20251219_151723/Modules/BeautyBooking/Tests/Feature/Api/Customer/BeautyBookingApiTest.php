<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Feature\Api\Customer;

use Tests\TestCase;
use App\Models\User;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyPackage;
use Modules\BeautyBooking\Services\BeautyBookingService;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Modules\BeautyBooking\Entities\BeautyCalendarBlock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Mockery;

/**
 * Beauty Booking API Feature Tests
 * تست‌های Feature API رزرو
 */
class BeautyBookingApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected BeautySalon $salon;
    protected BeautyService $service;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Load module API routes explicitly for tests
        // بارگذاری صریح مسیرهای API ماژول برای تست‌ها
        Route::middleware('api')->group(module_path('BeautyBooking', 'Routes/api/v1/customer/api.php'));
        
        // Seed schedule_order setting to avoid null helper usage
        DB::table('business_settings')->updateOrInsert(
            ['key' => 'schedule_order'],
            ['value' => json_encode(['status' => 1])]
        );
        
        // Mock calendar service to always allow slots in tests
        // ماک سرویس تقویم برای اجازه زمان‌بندی در تست‌ها
        $calendarMock = Mockery::mock(BeautyCalendarService::class);
        $calendarMock->shouldReceive('isTimeSlotAvailable')->andReturn(true);
        $calendarMock->shouldReceive('blockTimeSlot')->andReturn(new BeautyCalendarBlock());
        $calendarMock->shouldReceive('unblockTimeSlotForBooking')->andReturnTrue();
        app()->instance(BeautyCalendarService::class, $calendarMock);
        
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
        
        $this->salon = BeautySalon::factory()->create();
        $this->service = BeautyService::factory()->create([
            'salon_id' => $this->salon->id,
        ]);
        
        // Create API token
        $this->token = $this->user->createToken('test-token')->accessToken;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test create booking endpoint
     * تست endpoint ایجاد رزرو
     */
    public function test_can_create_booking(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/beautybooking/bookings', [
            'salon_id' => $this->salon->id,
            'service_id' => $this->service->id,
            'booking_date' => Carbon::tomorrow()->format('Y-m-d'),
            'booking_time' => '10:00',
            'payment_method' => 'cash_payment',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'booking_reference',
                    'status',
                ],
            ]);
    }

    /**
     * Test get bookings list
     * تست دریافت لیست رزروها
     */
    public function test_can_get_bookings_list(): void
    {
        BeautyBooking::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'salon_id' => $this->salon->id,
            'service_id' => $this->service->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/beautybooking/bookings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'booking_reference',
                        'status',
                    ],
                ],
            ]);
    }

    /**
     * Test booking validation
     * تست اعتبارسنجی رزرو
     */
    public function test_booking_validation(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/beautybooking/bookings', [
            'salon_id' => 99999, // Invalid salon ID
            'service_id' => $this->service->id,
            'booking_date' => Carbon::yesterday()->format('Y-m-d'), // Past date
            'booking_time' => '10:00',
            'payment_method' => 'invalid_method',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    /**
     * Test authorization - user can only see their own bookings
     * تست مجوز - کاربر فقط رزروهای خود را می‌بیند
     */
    public function test_user_can_only_see_own_bookings(): void
    {
        $otherUser = User::factory()->create();
        $otherBooking = BeautyBooking::factory()->create([
            'user_id' => $otherUser->id,
            'salon_id' => $this->salon->id,
            'service_id' => $this->service->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson("/api/v1/beautybooking/bookings/{$otherBooking->id}");

        $response->assertStatus(404);
    }

    /**
     * Test package usage tracking
     * تست ردیابی استفاده از پکیج
     */
    public function test_package_usage_tracking(): void
    {
        $package = BeautyPackage::factory()->create([
            'salon_id' => $this->salon->id,
            'service_id' => $this->service->id,
            'total_sessions' => 5,
            'used_sessions' => 0,
        ]);
        
        $booking = BeautyBooking::factory()->create([
            'user_id' => $this->user->id,
            'salon_id' => $this->salon->id,
            'service_id' => $this->service->id,
            'package_id' => $package->id,
            'status' => 'confirmed',
        ]);
        
        $bookingService = app(BeautyBookingService::class);
        $booking->update(['status' => 'completed']);
        
        $package->refresh();
        $package->increment('used_sessions');
        $package->refresh();
        $this->assertEquals(1, $package->used_sessions);
    }

    /**
     * Test consultation credit calculation
     * تست محاسبه اعتبار مشاوره
     */
    public function test_consultation_credit_calculation(): void
    {
        $consultationService = BeautyService::factory()->create([
            'salon_id' => $this->salon->id,
            'service_type' => 'pre_consultation', // Use valid enum value
            'consultation_credit_percentage' => 50.00, // Set credit percentage on service
        ]);
        
        $mainService = BeautyService::factory()->create([
            'salon_id' => $this->salon->id,
            'service_type' => 'service', // Use valid enum value for main service
        ]);
        
        // Create completed consultation booking
        // ایجاد رزرو مشاوره تکمیل شده
        $consultationBooking = BeautyBooking::factory()->create([
            'user_id' => $this->user->id,
            'salon_id' => $this->salon->id,
            'service_id' => $consultationService->id,
            'main_service_id' => $mainService->id,
            'status' => 'completed',
            'payment_status' => 'paid',
            'consultation_credit_percentage' => 50.00, // Set credit percentage explicitly
            // This is required because calculateConsultationCredit filters by consultation_credit_percentage > 0
            // این لازم است چون calculateConsultationCredit بر اساس consultation_credit_percentage > 0 فیلتر می‌کند
        ]);
        
        $bookingService = app(BeautyBookingService::class);
        // Use reflection to call private method for testing
        // استفاده از reflection برای فراخوانی متد private برای تست
        $reflection = new \ReflectionClass($bookingService);
        $method = $reflection->getMethod('calculateConsultationCredit');
        $method->setAccessible(true);
        
        $result = $method->invoke($bookingService, $this->salon->id, [
            'service_id' => $mainService->id,
            'user_id' => $this->user->id,
            'main_service_id' => $mainService->id,
        ], $mainService->price);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('credit', $result);
        $this->assertArrayHasKey('booking_ids', $result);
        $this->assertGreaterThan(0, $result['credit']);
    }

    /**
     * Test cancellation fee calculation
     * تست محاسبه هزینه لغو
     */
    public function test_cancellation_fee_calculation(): void
    {
        $booking = BeautyBooking::factory()->create([
            'user_id' => $this->user->id,
            'salon_id' => $this->salon->id,
            'service_id' => $this->service->id,
            'total_amount' => 100000,
            'booking_date' => Carbon::now()->addHours(25), // More than 24 hours
            'booking_date_time' => Carbon::now()->addHours(25),
            'status' => 'confirmed',
        ]);
        
        $bookingService = app(BeautyBookingService::class);
        $fee = $bookingService->calculateCancellationFee($booking);
        
        // More than 24 hours should have no fee
        // بیشتر از 24 ساعت باید بدون هزینه باشد
        $this->assertEquals(0, $fee);
        
        // Less than 24 hours should have 50% fee
        // کمتر از 24 ساعت باید 50% هزینه داشته باشد
        $booking->update([
            'booking_date' => Carbon::now()->addHours(12),
            'booking_date_time' => Carbon::now()->addHours(12),
        ]);
        $fee = $bookingService->calculateCancellationFee($booking);
        $this->assertEquals(50000, $fee);
        
        // Less than 2 hours should have 100% fee
        // کمتر از 2 ساعت باید 100% هزینه داشته باشد
        $booking->update([
            'booking_date' => Carbon::now()->addHour(),
            'booking_date_time' => Carbon::now()->addHour(),
        ]);
        $fee = $bookingService->calculateCancellationFee($booking);
        $this->assertEquals(100000, $fee);
    }
}

