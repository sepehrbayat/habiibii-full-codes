<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Unit;

use Tests\TestCase;
use Modules\BeautyBooking\Services\BeautyBookingService;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyPackage;
use Modules\BeautyBooking\Entities\BeautyTransaction;
use Modules\BeautyBooking\Entities\BeautyCalendarBlock;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Mockery;

/**
 * Beauty Booking Service Unit Tests
 * تست‌های واحد سرویس رزرو
 */
class BeautyBookingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BeautyBookingService $bookingService;

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
        
        // Ensure an admin exists for wallet-related flows
        // اطمینان از وجود ادمین برای جریان‌های مرتبط با کیف پول
        DB::table('admins')->updateOrInsert(
            ['id' => 1],
            [
                'role_id' => 1,
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test booking creation
     * تست ایجاد رزرو
     */
    public function test_can_create_booking(): void
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        $salon = BeautySalon::factory()->create();
        $service = BeautyService::factory()->create(['salon_id' => $salon->id]);
        
        $bookingData = [
            'service_id' => $service->id,
            'booking_date' => Carbon::tomorrow()->format('Y-m-d'),
            'booking_time' => '10:00',
            'payment_method' => 'cash_payment',
        ];

        // Act
        $booking = $this->bookingService->createBooking(
            $user->id,
            $salon->id,
            $bookingData
        );

        // Assert
        $this->assertInstanceOf(BeautyBooking::class, $booking);
        $this->assertEquals($user->id, $booking->user_id);
        $this->assertEquals($salon->id, $booking->salon_id);
        $this->assertEquals('pending', $booking->status);
    }

    /**
     * Test cancellation fee calculation
     * تست محاسبه هزینه لغو
     */
    public function test_cancellation_fee_calculation(): void
    {
        // Arrange
        $booking = BeautyBooking::factory()->create([
            'booking_date' => Carbon::now()->addHours(12)->format('Y-m-d'),
            'booking_time' => Carbon::now()->addHours(12)->format('H:i'),
            'total_amount' => 100.00,
        ]);

        // Act
        $reflection = new \ReflectionClass($this->bookingService);
        $method = $reflection->getMethod('calculateCancellationFee');
        $method->setAccessible(true);
        $fee = $method->invoke($this->bookingService, $booking);

        // Assert
        $this->assertIsFloat($fee);
        $this->assertGreaterThanOrEqual(0, $fee);
        $this->assertLessThanOrEqual($booking->total_amount, $fee);
    }

    /**
     * Test package usage tracking
     * تست ردیابی استفاده از پکیج
     */
    public function test_package_usage_tracking(): void
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        $salon = BeautySalon::factory()->create();
        $service = BeautyService::factory()->create(['salon_id' => $salon->id]);
        $package = BeautyPackage::factory()->create([
            'salon_id' => $salon->id,
            'service_id' => $service->id,
            'sessions_count' => 5,
        ]);
        
        $booking = BeautyBooking::factory()->create([
            'user_id' => $user->id,
            'salon_id' => $salon->id,
            'service_id' => $service->id,
            'package_id' => $package->id,
            'status' => 'completed',
        ]);

        // Act
        $reflection = new \ReflectionClass($this->bookingService);
        $method = $reflection->getMethod('trackPackageUsage');
        $method->setAccessible(true);
        $method->invoke($this->bookingService, $booking);

        // Assert
        $this->assertDatabaseHas('beauty_package_usages', [
            'package_id' => $package->id,
            'user_id' => $user->id,
            'booking_id' => $booking->id,
            'status' => 'used',
        ]);
    }

    /**
     * Test that double updatePaymentStatus does not create duplicate transactions
     * تست اینکه به‌روزرسانی دوباره وضعیت پرداخت تراکنش‌های تکراری ایجاد نمی‌کند
     */
    public function test_double_update_payment_status_does_not_create_duplicate_transactions(): void
    {
        // Arrange
        $booking = BeautyBooking::factory()->create([
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'total_amount' => 100.00,
            'commission_amount' => 10.00,
            'service_fee' => 2.00,
        ]);

        // Act - First payment status update
        $this->bookingService->updatePaymentStatus($booking, 'paid');
        
        // Get transaction count after first update
        $firstUpdateCount = BeautyTransaction::where('booking_id', $booking->id)
            ->where('transaction_type', 'commission')
            ->count();
        
        // Act - Second payment status update (should not create duplicates)
        $this->bookingService->updatePaymentStatus($booking->fresh(), 'paid');
        
        // Assert - Transaction count should remain the same
        $secondUpdateCount = BeautyTransaction::where('booking_id', $booking->id)
            ->where('transaction_type', 'commission')
            ->count();
        
        $this->assertEquals($firstUpdateCount, $secondUpdateCount, 'Duplicate transactions should not be created');
        $this->assertEquals(1, $firstUpdateCount, 'Should have exactly one commission transaction');
    }

    /**
     * Test invalid status transitions throw exception
     * تست اینکه انتقال‌های وضعیت نامعتبر استثنا پرتاب می‌کنند
     */
    public function test_invalid_status_transitions_throw_exception(): void
    {
        // Arrange
        $booking = BeautyBooking::factory()->create([
            'status' => 'completed', // Terminal state
        ]);

        // Act & Assert - Should throw exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(translate('messages.invalid_status_transition'));
        
        $this->bookingService->updateBookingStatus($booking, 'pending'); // Cannot go back from completed
    }

    /**
     * Test valid status transitions succeed
     * تست اینکه انتقال‌های وضعیت معتبر موفق می‌شوند
     */
    public function test_valid_status_transitions_succeed(): void
    {
        // Arrange
        $booking = BeautyBooking::factory()->create([
            'status' => 'pending',
            'booking_date' => \Carbon\Carbon::now()->subHour()->format('Y-m-d'),
            'booking_time' => \Carbon\Carbon::now()->subHour()->format('H:i'),
            'booking_date_time' => \Carbon\Carbon::now()->subHour(),
            'payment_status' => 'paid',
        ]);

        // Act - Transition from pending to confirmed
        $updatedBooking = $this->bookingService->updateBookingStatus($booking, 'confirmed');

        // Assert
        $this->assertEquals('confirmed', $updatedBooking->status);
        
        // Act - Transition from confirmed to completed
        $updatedBooking->update(['payment_status' => 'paid']);
        $completedBooking = $this->bookingService->updateBookingStatus($updatedBooking, 'completed');

        // Assert
        $this->assertEquals('completed', $completedBooking->status);
    }

    /**
     * Test concurrent booking creation prevents double booking
     * تست اینکه ایجاد همزمان رزرو از double booking جلوگیری می‌کند
     */
    public function test_concurrent_booking_creation_prevents_double_booking(): void
    {
        // Arrange
        $user1 = \App\Models\User::factory()->create();
        $user2 = \App\Models\User::factory()->create();
        $salon = BeautySalon::factory()->create();
        $service = BeautyService::factory()->create([
            'salon_id' => $salon->id,
            'duration_minutes' => 60,
        ]);
        
        $bookingDate = Carbon::tomorrow()->format('Y-m-d');
        $bookingTime = '10:00';
        
        $bookingData = [
            'service_id' => $service->id,
            'booking_date' => $bookingDate,
            'booking_time' => $bookingTime,
            'payment_method' => 'cash_payment',
        ];

        // Act - Create first booking
        $booking1 = $this->bookingService->createBooking(
            $user1->id,
            $salon->id,
            $bookingData
        );

        // Assert - First booking should succeed
        $this->assertInstanceOf(BeautyBooking::class, $booking1);

        // Act & Assert - Second booking for same time slot should fail
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(translate('messages.time_slot_not_available'));
        
        $this->bookingService->createBooking(
            $user2->id,
            $salon->id,
            $bookingData
        );
    }

    /**
     * Test cancellation fee matrix is correct
     * تست اینکه ماتریس هزینه لغو صحیح است
     */
    public function test_cancellation_fee_matrix_correct(): void
    {
        // Arrange - Booking more than 24 hours away
        $booking24h = BeautyBooking::factory()->create([
            'booking_date' => Carbon::now()->addHours(25)->format('Y-m-d'),
            'booking_time' => Carbon::now()->addHours(25)->format('H:i'),
            'booking_date_time' => Carbon::now()->addHours(25),
            'total_amount' => 100.00,
        ]);

        // Arrange - Booking 2-24 hours away
        $booking12h = BeautyBooking::factory()->create([
            'booking_date' => Carbon::now()->addHours(12)->format('Y-m-d'),
            'booking_time' => Carbon::now()->addHours(12)->format('H:i'),
            'booking_date_time' => Carbon::now()->addHours(12),
            'total_amount' => 100.00,
        ]);

        // Arrange - Booking less than 2 hours away
        $booking1h = BeautyBooking::factory()->create([
            'booking_date' => Carbon::now()->addHour()->format('Y-m-d'),
            'booking_time' => Carbon::now()->addHour()->format('H:i'),
            'booking_date_time' => Carbon::now()->addHour(),
            'total_amount' => 100.00,
        ]);

        // Arrange - Past booking
        $bookingPast = BeautyBooking::factory()->create([
            'booking_date' => Carbon::yesterday()->format('Y-m-d'),
            'booking_time' => Carbon::yesterday()->format('H:i'),
            'booking_date_time' => Carbon::yesterday(),
            'total_amount' => 100.00,
        ]);

        // Act
        $reflection = new \ReflectionClass($this->bookingService);
        $method = $reflection->getMethod('calculateCancellationFee');
        $method->setAccessible(true);
        
        $fee24h = $method->invoke($this->bookingService, $booking24h);
        $fee12h = $method->invoke($this->bookingService, $booking12h);
        $fee1h = $method->invoke($this->bookingService, $booking1h);
        $feePast = $method->invoke($this->bookingService, $bookingPast);

        // Assert - More than 24 hours: 0% fee
        $this->assertEquals(0.0, $fee24h, 'Cancellation more than 24h before should have 0% fee');

        // Assert - 2-24 hours: 50% fee (default config)
        $expectedPartialFee = 100.00 * 0.5; // 50% of total
        $this->assertEquals($expectedPartialFee, $fee12h, 'Cancellation 2-24h before should have 50% fee');

        // Assert - Less than 2 hours: 100% fee
        $this->assertEquals(100.00, $fee1h, 'Cancellation less than 2h before should have 100% fee');

        // Assert - Past booking: 100% fee
        $this->assertEquals(100.00, $feePast, 'Past booking cancellation should have 100% fee');
    }
}

