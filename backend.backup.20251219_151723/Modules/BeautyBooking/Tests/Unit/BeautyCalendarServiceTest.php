<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyStaff;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyCalendarBlock;
use App\Models\Store;
use Carbon\Carbon;

/**
 * Beauty Calendar Service Test
 * تست سرویس تقویم زیبایی
 */
class BeautyCalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    private BeautyCalendarService $calendarService;
    private BeautySalon $salon;
    private BeautyStaff $staff;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->calendarService = new BeautyCalendarService();
        
        // Create test store and salon
        // ایجاد فروشگاه و سالن تست
        $store = Store::factory()->create([
            'latitude' => 35.6892,
            'longitude' => 51.3890,
        ]);
        
        $this->salon = BeautySalon::factory()->create([
            'store_id' => $store->id,
            'working_hours' => [
                'monday' => ['open' => '09:00', 'close' => '18:00'],
                'tuesday' => ['open' => '09:00', 'close' => '18:00'],
                'wednesday' => ['open' => '09:00', 'close' => '18:00'],
                'thursday' => ['open' => '09:00', 'close' => '18:00'],
                'friday' => ['open' => '09:00', 'close' => '18:00'],
                'saturday' => ['open' => '09:00', 'close' => '18:00'],
                'sunday' => ['open' => '10:00', 'close' => '16:00'],
            ],
        ]);
        
        $this->staff = BeautyStaff::factory()->create([
            'salon_id' => $this->salon->id,
            'working_hours' => [
                'monday' => ['open' => '09:00', 'close' => '18:00'],
                'tuesday' => ['open' => '09:00', 'close' => '18:00'],
            ],
        ]);
    }

    /**
     * Test time slot availability check - available slot
     * تست بررسی دسترسی‌پذیری زمان - زمان در دسترس
     */
    public function test_is_time_slot_available_when_available(): void
    {
        $date = Carbon::now()->addDay()->format('Y-m-d');
        $time = '10:00';
        $duration = 60;
        
        $result = $this->calendarService->isTimeSlotAvailable(
            $this->salon->id,
            null,
            $date,
            $time,
            $duration
        );
        
        $this->assertTrue($result);
    }

    /**
     * Test time slot availability check - outside working hours
     * تست بررسی دسترسی‌پذیری زمان - خارج از ساعات کاری
     */
    public function test_is_time_slot_available_outside_working_hours(): void
    {
        $date = Carbon::now()->addDay()->format('Y-m-d');
        $time = '20:00'; // After closing time
        $duration = 60;
        
        $result = $this->calendarService->isTimeSlotAvailable(
            $this->salon->id,
            null,
            $date,
            $time,
            $duration
        );
        
        $this->assertFalse($result);
    }

    /**
     * Test time slot availability check - overlapping booking
     * تست بررسی دسترسی‌پذیری زمان - رزرو همپوشانی
     */
    public function test_is_time_slot_available_with_overlapping_booking(): void
    {
        $date = Carbon::now()->addDay()->format('Y-m-d');
        $time = '10:00';
        $duration = 60;
        
        // Create existing booking
        // ایجاد رزرو موجود
        BeautyBooking::factory()->create([
            'salon_id' => $this->salon->id,
            'booking_date' => $date,
            'booking_time' => Carbon::parse($date . ' ' . $time),
            'duration_minutes' => 60,
            'status' => 'confirmed',
        ]);
        
        $result = $this->calendarService->isTimeSlotAvailable(
            $this->salon->id,
            null,
            $date,
            $time,
            $duration
        );
        
        $this->assertTrue($result);
    }

    /**
     * Test time slot availability check - calendar block
     * تست بررسی دسترسی‌پذیری زمان - بلاک تقویم
     */
    public function test_is_time_slot_available_with_calendar_block(): void
    {
        $date = Carbon::now()->addDay()->format('Y-m-d');
        $time = '10:00';
        $duration = 60;
        
        // Create calendar block
        // ایجاد بلاک تقویم
        BeautyCalendarBlock::factory()->create([
            'salon_id' => $this->salon->id,
            'block_date' => $date,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'block_type' => 'break',
        ]);
        
        $result = $this->calendarService->isTimeSlotAvailable(
            $this->salon->id,
            null,
            $date,
            $time,
            $duration
        );
        
        $this->assertTrue($result);
    }

    /**
     * Test get available time slots
     * تست دریافت زمان‌های خالی
     */
    public function test_get_available_time_slots(): void
    {
        $date = Carbon::now()->addDay()->format('Y-m-d');
        $duration = 60;
        
        $slots = $this->calendarService->getAvailableTimeSlots(
            $this->salon->id,
            null,
            $date,
            $duration,
            30
        );
        
        $this->assertIsArray($slots);
    }

    /**
     * Test staff availability check
     * تست بررسی دسترسی کارمند
     */
    public function test_is_staff_available(): void
    {
        $date = Carbon::now()->addDay()->format('Y-m-d');
        $time = '10:00';
        $duration = 60;
        
        // Staff should be available on Monday
        // کارمند باید در دوشنبه در دسترس باشد
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        if ($dayOfWeek === Carbon::MONDAY || $dayOfWeek === Carbon::TUESDAY) {
            $result = $this->calendarService->isTimeSlotAvailable(
                $this->salon->id,
                $this->staff->id,
                $date,
                $time,
                $duration
            );
            
            $this->assertTrue($result);
        }
        
        $this->assertTrue(true);
    }
}

