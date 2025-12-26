<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Feature\Api\Vendor;

use Tests\TestCase;
use App\Models\Vendor;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyService;
use Illuminate\Support\Str;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Modules\BeautyBooking\Entities\BeautyCalendarBlock;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Beauty Booking Vendor API Feature Tests
 * تست‌های Feature API رزرو برای فروشنده
 */
class BeautyBookingVendorApiTest extends TestCase
{
    use RefreshDatabase;

    protected Vendor $vendor;
    protected BeautySalon $salon;
    protected BeautyService $service;
    protected BeautyBooking $booking;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Load module vendor routes with correct prefix
        Route::prefix('api/v1/beautybooking/vendor')
            ->middleware('api')
            ->group(module_path('BeautyBooking', 'Routes/api/v1/vendor/api.php'));
        
        // Mock calendar service
        $calendarMock = Mockery::mock(BeautyCalendarService::class);
        $calendarMock->shouldReceive('isTimeSlotAvailable')->andReturn(true);
        $calendarMock->shouldReceive('blockTimeSlot')->andReturn(BeautyCalendarBlock::factory()->make(['id' => 1]));
        $calendarMock->shouldReceive('unblockTimeSlotForBooking')->andReturnTrue();
        $calendarMock->shouldReceive('getAvailableTimeSlots')->andReturn([['start' => '10:00', 'end' => '11:00']]);
        $calendarMock->shouldReceive('unblockTimeSlot')->andReturnTrue();
        app()->instance(BeautyCalendarService::class, $calendarMock);
        
        DB::table('business_settings')->updateOrInsert(
            ['key' => 'schedule_order'],
            ['value' => json_encode(['status' => 1])]
        );
        
        $this->vendor = Vendor::factory()->create([
            'email' => uniqid('vendor_bkg_', true) . '@example.com',
        ]);
        $store = \App\Models\Store::factory()->create([
            'vendor_id' => $this->vendor->id,
        ]);
        
        $this->salon = BeautySalon::factory()->create([
            'store_id' => $store->id,
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
        
        $this->service = BeautyService::factory()->create([
            'salon_id' => $this->salon->id,
        ]);
        
        $this->booking = BeautyBooking::factory()->create([
            'salon_id' => $this->salon->id,
            'service_id' => $this->service->id,
            'status' => 'pending',
            'booking_date_time' => \Carbon\Carbon::now()->addDay(),
        ]);
        
        // Create vendor API token (auth_token used by vendor.api middleware)
        $this->token = Str::random(60);
        $this->vendor->forceFill(['auth_token' => $this->token])->save();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test vendor can list their bookings
     * تست اینکه فروشنده می‌تواند لیست رزروهای خود را ببیند
     */
    public function test_vendor_can_list_bookings(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
            'vendorType' => 'owner',
        ])->getJson('/api/v1/beautybooking/vendor/bookings/list/all');

        $response->assertStatus(200);
    }

    /**
     * Test vendor can confirm booking
     * تست اینکه فروشنده می‌تواند رزرو را تأیید کند
     */
    public function test_vendor_can_confirm_booking(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
            'vendorType' => 'owner',
        ])->putJson('/api/v1/beautybooking/vendor/bookings/confirm', [
            'booking_id' => $this->booking->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'confirmed',
                ],
            ]);
    }

    /**
     * Test vendor authorization - can only access own salon bookings
     * تست مجوز فروشنده - فقط می‌تواند به رزروهای سالن خود دسترسی داشته باشد
     */
    public function test_vendor_can_only_access_own_salon_bookings(): void
    {
        $otherVendor = Vendor::factory()->create();
        $otherStore = \App\Models\Store::factory()->create([
            'vendor_id' => $otherVendor->id,
        ]);
        $otherSalon = BeautySalon::factory()->create([
            'store_id' => $otherStore->id,
        ]);
        $otherBooking = BeautyBooking::factory()->create([
            'salon_id' => $otherSalon->id,
            'service_id' => $this->service->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
            'vendorType' => 'owner',
        ])->putJson('/api/v1/beautybooking/vendor/bookings/confirm', [
            'booking_id' => $otherBooking->id,
        ]);

        $response->assertStatus(404);
    }
}

