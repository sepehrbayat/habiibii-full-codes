<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyCalendarBlock;
use Modules\BeautyBooking\Services\BeautyBookingService;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use App\Models\User;
use App\Models\Store;
use Carbon\Carbon;
use Mockery;

/**
 * Booking Flow Test
 * تست جریان رزرو
 *
 * Tests complete booking creation flow
 * تست جریان کامل ایجاد رزرو
 */
class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private BeautyBookingService $bookingService;
    private User $user;
    private BeautySalon $salon;
    private BeautyService $service;

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
        
        $this->bookingService = app(BeautyBookingService::class);
        
        // Create test user
        // ایجاد کاربر تست
        $this->user = User::factory()->create();
        
        // Create test store and salon
        // ایجاد فروشگاه و سالن تست
        $store = Store::factory()->create();
        $this->salon = BeautySalon::factory()->create([
            'store_id' => $store->id,
            'verification_status' => 1,
            'is_verified' => true,
        ]);
        // Set generous working hours to allow test slots
        $this->salon->update([
            'working_hours' => [
                ['day' => 'monday', 'open' => '00:00', 'close' => '23:59'],
                ['day' => 'tuesday', 'open' => '00:00', 'close' => '23:59'],
                ['day' => 'wednesday', 'open' => '00:00', 'close' => '23:59'],
                ['day' => 'thursday', 'open' => '00:00', 'close' => '23:59'],
                ['day' => 'friday', 'open' => '00:00', 'close' => '23:59'],
                ['day' => 'saturday', 'open' => '00:00', 'close' => '23:59'],
                ['day' => 'sunday', 'open' => '00:00', 'close' => '23:59'],
            ],
        ]);
        
        // Create test service
        // ایجاد خدمت تست
        $this->service = BeautyService::factory()->create([
            'salon_id' => $this->salon->id,
            'status' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test complete booking creation flow
     * تست جریان کامل ایجاد رزرو
     *
     * @return void
     */
    public function test_complete_booking_creation_flow(): void
    {
        $bookingDate = Carbon::tomorrow()->format('Y-m-d');
        $bookingTime = '10:00';
        
        $bookingData = [
            'service_id' => $this->service->id,
            'booking_date' => $bookingDate,
            'booking_time' => $bookingTime,
            'payment_method' => 'cash_payment',
        ];
        
        $booking = $this->bookingService->createBooking(
            $this->user->id,
            $this->salon->id,
            $bookingData
        );
        
        $this->assertInstanceOf(BeautyBooking::class, $booking);
        $this->assertEquals('pending', $booking->status);
        $this->assertEquals($this->user->id, $booking->user_id);
        $this->assertEquals($this->salon->id, $booking->salon_id);
        $this->assertNotNull($booking->booking_reference);
    }

    /**
     * Test booking cancellation with fee calculation
     * تست لغو رزرو با محاسبه جریمه
     *
     * @return void
     */
    public function test_booking_cancellation_with_fee(): void
    {
        $bookingDate = Carbon::now()->addHours(12)->format('Y-m-d');
        $bookingTime = Carbon::now()->addHours(12)->format('H:i');
        
        $bookingData = [
            'service_id' => $this->service->id,
            'booking_date' => $bookingDate,
            'booking_time' => $bookingTime,
            'payment_method' => 'cash_payment',
        ];
        
        $booking = $this->bookingService->createBooking(
            $this->user->id,
            $this->salon->id,
            $bookingData
        );
        
        // Cancel booking (less than 24 hours - should have fee)
        // لغو رزرو (کمتر از 24 ساعت - باید جریمه داشته باشد)
        $cancelledBooking = $this->bookingService->cancelBooking($booking, 'Test cancellation');
        
        $this->assertEquals('cancelled', $cancelledBooking->status);
        $this->assertGreaterThan(0, $cancelledBooking->cancellation_fee);
    }
}

