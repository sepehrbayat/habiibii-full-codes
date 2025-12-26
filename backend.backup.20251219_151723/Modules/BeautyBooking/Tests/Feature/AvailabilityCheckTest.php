<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Carbon\Carbon;

/**
 * Availability Check Test
 * تست بررسی دسترسی‌پذیری
 *
 * Tests calendar availability logic
 * تست منطق دسترسی‌پذیری تقویم
 */
class AvailabilityCheckTest extends TestCase
{
    use RefreshDatabase;

    private BeautyCalendarService $calendarService;
    private BeautySalon $salon;
    private BeautyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->calendarService = app(BeautyCalendarService::class);
        
        $store = \App\Models\Store::factory()->create();
        $this->salon = BeautySalon::factory()->create([
            'store_id' => $store->id,
            'working_hours' => [
                'monday' => ['open' => '09:00', 'close' => '18:00'],
                'tuesday' => ['open' => '09:00', 'close' => '18:00'],
            ],
        ]);
        
        $this->service = BeautyService::factory()->create([
            'salon_id' => $this->salon->id,
            'duration_minutes' => 60,
        ]);
    }

    /**
     * Test time slot availability check
     * تست بررسی دسترسی‌پذیری زمان
     *
     * @return void
     */
    public function test_time_slot_availability_check(): void
    {
        $date = Carbon::tomorrow()->format('Y-m-d');
        $time = '10:00';
        
        $isAvailable = $this->calendarService->isTimeSlotAvailable(
            $this->salon->id,
            null,
            $date,
            $time,
            $this->service->duration_minutes
        );
        
        $this->assertIsBool($isAvailable);
    }

    /**
     * Test holiday blocking
     * تست بلاک کردن تعطیلات
     *
     * @return void
     */
    public function test_holiday_blocking(): void
    {
        $holidayDate = Carbon::tomorrow()->format('Y-m-d');
        
        $this->salon->update([
            'holidays' => [$holidayDate],
        ]);
        
        $isAvailable = $this->calendarService->isTimeSlotAvailable(
            $this->salon->id,
            null,
            $holidayDate,
            '10:00',
            $this->service->duration_minutes
        );
        
        $this->assertFalse($isAvailable);
    }
}

