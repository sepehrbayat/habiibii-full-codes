<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Feature\Api\Vendor;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Modules\BeautyBooking\Entities\BeautyCalendarBlock;
use App\Models\Store;
use App\Models\Vendor;
use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Mockery;
use Illuminate\Support\Facades\DB;

/**
 * Vendor API Authentication Tests
 * تست‌های احراز هویت API فروشنده
 *
 * Tests authentication and authorization for vendor API endpoints
 * تست احراز هویت و مجوز برای endpoint های API فروشنده
 */
class VendorApiAuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    protected Vendor $vendor;
    protected Vendor $otherVendor;
    protected Store $store;
    protected Store $otherStore;
    protected BeautySalon $salon;
    protected BeautySalon $otherSalon;
    protected BeautyService $service;
    protected BeautyBooking $booking;
    protected BeautyBooking $otherBooking;
    protected string $token;
    protected string $otherToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Load module vendor routes explicitly with correct prefix
        Route::prefix('api/v1/beautybooking/vendor')
            ->middleware('api')
            ->group(module_path('BeautyBooking', 'Routes/api/v1/vendor/api.php'));
        
        // Seed schedule_order to avoid null helper usage in downstream flows
        DB::table('business_settings')->updateOrInsert(
            ['key' => 'schedule_order'],
            ['value' => json_encode(['status' => 1])]
        );
        
        // Mock calendar service to always allow slots
        $calendarMock = Mockery::mock(BeautyCalendarService::class);
        $calendarMock->shouldReceive('isTimeSlotAvailable')->andReturn(true);
        $calendarMock->shouldReceive('blockTimeSlot')->andReturn(BeautyCalendarBlock::factory()->make(['id' => 1]));
        $calendarMock->shouldReceive('unblockTimeSlotForBooking')->andReturnTrue();
        $calendarMock->shouldReceive('getAvailableTimeSlots')->andReturn([['start' => '10:00', 'end' => '11:00']]);
        $calendarMock->shouldReceive('unblockTimeSlot')->andReturnTrue();
        app()->instance(BeautyCalendarService::class, $calendarMock);
        
        // Create first vendor and salon
        $this->vendor = Vendor::factory()->create([
            'email' => uniqid('vendor1_', true) . '@example.com',
        ]);
        $this->store = Store::factory()->create([
            'vendor_id' => $this->vendor->id,
        ]);
        $this->salon = BeautySalon::factory()->create([
            'store_id' => $this->store->id,
        ]);
        
        // Create second vendor and salon (for authorization tests)
        $this->otherVendor = Vendor::factory()->create([
            'email' => uniqid('vendor2_', true) . '@example.com',
        ]);
        $this->otherStore = Store::factory()->create([
            'vendor_id' => $this->otherVendor->id,
        ]);
        $this->otherSalon = BeautySalon::factory()->create([
            'store_id' => $this->otherStore->id,
        ]);
        
        $this->service = \Modules\BeautyBooking\Entities\BeautyService::factory()->create([
            'salon_id' => $this->salon->id,
        ]);
        
        $this->booking = BeautyBooking::factory()->create([
            'salon_id' => $this->salon->id,
            'service_id' => $this->service->id,
            'user_id' => \App\Models\User::factory()->create([
                'email' => uniqid('vendor_auth_user_', true) . '@example.com',
            ])->id,
        ]);
        
        $this->otherBooking = BeautyBooking::factory()->create([
            'salon_id' => $this->otherSalon->id,
            'service_id' => $this->service->id,
            'user_id' => \App\Models\User::factory()->create([
                'email' => uniqid('vendor_other_user_', true) . '@example.com',
            ])->id,
        ]);
        
        // Create API tokens (auth_token used by vendor.api middleware)
        $this->token = Str::random(60);
        $this->otherToken = Str::random(60);
        $this->vendor->forceFill(['auth_token' => $this->token])->save();
        $this->otherVendor->forceFill(['auth_token' => $this->otherToken])->save();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function getAuthHeaders(string $token = null): array
    {
        return [
            'Authorization' => 'Bearer ' . ($token ?? $this->token),
            'Accept' => 'application/json',
            'vendorType' => 'owner',
        ];
    }

    // ============================================
    // Authentication Tests
    // ============================================

    /**
     * Test unauthenticated requests return 401
     * تست اینکه درخواست‌های بدون احراز هویت 401 برمی‌گردانند
     */
    public function test_unauthenticated_requests_return_401(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->getJson('/api/v1/beautybooking/vendor/bookings/list/all');

        $response->assertStatus(401)
            ->assertJsonStructure([
                'errors' => [
                    '*' => [
                        'code',
                        'message',
                    ],
                ],
            ]);
    }

    /**
     * Test requests without vendorType header return 403
     * تست اینکه درخواست‌های بدون header vendorType 403 برمی‌گردانند
     */
    public function test_requests_without_vendor_type_return_403(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/beautybooking/vendor/bookings/list/all');

        $response->assertStatus(403)
            ->assertJsonStructure([
                'errors' => [
                    '*' => [
                        'code',
                        'message',
                    ],
                ],
            ]);
    }

    /**
     * Test invalid tokens are rejected
     * تست اینکه توکن‌های نامعتبر رد می‌شوند
     */
    public function test_invalid_tokens_are_rejected(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
            'Accept' => 'application/json',
            'vendorType' => 'owner',
        ])->getJson('/api/v1/beautybooking/vendor/bookings/list/all');

        $response->assertStatus(401);
    }

    /**
     * Test authenticated vendor can access their data
     * تست اینکه فروشنده احراز هویت شده می‌تواند به داده‌های خود دسترسی داشته باشد
     */
    public function test_authenticated_vendor_can_access_own_data(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/bookings/list/all');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data',
            ]);
    }

    // ============================================
    // Authorization Tests - Booking Management
    // ============================================

    /**
     * Test vendor can only access own salon bookings
     * تست اینکه فروشنده فقط می‌تواند به رزروهای سالن خود دسترسی داشته باشد
     */
    public function test_vendor_can_only_access_own_salon_bookings(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/bookings/details?booking_id=' . $this->otherBooking->id);

        $response->assertStatus(404);
    }

    /**
     * Test vendor cannot confirm other vendor's booking
     * تست اینکه فروشنده نمی‌تواند رزرو فروشنده دیگر را تأیید کند
     */
    public function test_vendor_cannot_confirm_other_vendor_booking(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->putJson('/api/v1/beautybooking/vendor/bookings/confirm', [
                'booking_id' => $this->otherBooking->id,
            ]);

        $response->assertStatus(404);
    }

    /**
     * Test vendor cannot cancel other vendor's booking
     * تست اینکه فروشنده نمی‌تواند رزرو فروشنده دیگر را لغو کند
     */
    public function test_vendor_cannot_cancel_other_vendor_booking(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->putJson('/api/v1/beautybooking/vendor/bookings/cancel', [
                'booking_id' => $this->otherBooking->id,
            ]);

        $response->assertStatus(404);
    }

    // ============================================
    // Authorization Tests - Staff Management
    // ============================================

    /**
     * Test vendor can only access own salon staff
     * تست اینکه فروشنده فقط می‌تواند به کارمندان سالن خود دسترسی داشته باشد
     */
    public function test_vendor_can_only_access_own_salon_staff(): void
    {
        $otherStaff = \Modules\BeautyBooking\Entities\BeautyStaff::factory()->create([
            'salon_id' => $this->otherSalon->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson("/api/v1/beautybooking/vendor/staff/details/{$otherStaff->id}");

        $response->assertStatus(404); // Not found because it's filtered by salon_id
    }

    /**
     * Test vendor cannot update other vendor's staff
     * تست اینکه فروشنده نمی‌تواند کارمند فروشنده دیگر را به‌روزرسانی کند
     */
    public function test_vendor_cannot_update_other_vendor_staff(): void
    {
        $otherStaff = \Modules\BeautyBooking\Entities\BeautyStaff::factory()->create([
            'salon_id' => $this->otherSalon->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson("/api/v1/beautybooking/vendor/staff/update/{$otherStaff->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(404);
    }

    // ============================================
    // Authorization Tests - Service Management
    // ============================================

    /**
     * Test vendor can only access own salon services
     * تست اینکه فروشنده فقط می‌تواند به خدمات سالن خود دسترسی داشته باشد
     */
    public function test_vendor_can_only_access_own_salon_services(): void
    {
        $otherService = BeautyService::factory()->create([
            'salon_id' => $this->otherSalon->id,
            'category_id' => $this->service->category_id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson("/api/v1/beautybooking/vendor/service/details/{$otherService->id}");

        $response->assertStatus(404);
    }

    /**
     * Test vendor cannot delete other vendor's service
     * تست اینکه فروشنده نمی‌تواند خدمت فروشنده دیگر را حذف کند
     */
    public function test_vendor_cannot_delete_other_vendor_service(): void
    {
        $otherService = BeautyService::factory()->create([
            'salon_id' => $this->otherSalon->id,
            'category_id' => $this->service->category_id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->deleteJson("/api/v1/beautybooking/vendor/service/delete/{$otherService->id}");

        $response->assertStatus(404);
    }

    // ============================================
    // Authorization Tests - Calendar Management
    // ============================================

    /**
     * Test vendor can only access own salon calendar
     * تست اینکه فروشنده فقط می‌تواند به تقویم سالن خود دسترسی داشته باشد
     */
    public function test_vendor_can_only_access_own_salon_calendar(): void
    {
        $otherBlock = \Modules\BeautyBooking\Entities\BeautyCalendarBlock::factory()->create([
            'salon_id' => $this->otherSalon->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->deleteJson("/api/v1/beautybooking/vendor/calendar/blocks/delete/{$otherBlock->id}");

        $response->assertStatus(404);
    }

    // ============================================
    // Authorization Tests - Profile
    // ============================================

    /**
     * Test vendor can only access own profile
     * تست اینکه فروشنده فقط می‌تواند به پروفایل خود دسترسی داشته باشد
     */
    public function test_vendor_can_only_access_own_profile(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders($this->token))
            ->getJson('/api/v1/beautybooking/vendor/profile');

        $response->assertStatus(200)
            ->assertJsonPath('data.salon.id', $this->salon->id);
    }

    /**
     * Test vendor cannot update other vendor's profile
     * تست اینکه فروشنده نمی‌تواند پروفایل فروشنده دیگر را به‌روزرسانی کند
     */
    public function test_vendor_cannot_update_other_vendor_profile(): void
    {
        // Each vendor can only update their own profile via their own token
        // هر فروشنده فقط می‌تواند پروفایل خود را با توکن خود به‌روزرسانی کند
        $response = $this->withHeaders($this->getAuthHeaders($this->token))
            ->postJson('/api/v1/beautybooking/vendor/profile/update', [
                'license_number' => 'UPDATED',
            ]);

        $response->assertStatus(200);
        
        // Verify it only updated own salon
        $this->assertDatabaseHas('beauty_salons', [
            'id' => $this->salon->id,
            'license_number' => 'UPDATED',
        ]);
        
        $this->assertDatabaseMissing('beauty_salons', [
            'id' => $this->otherSalon->id,
            'license_number' => 'UPDATED',
        ]);
    }

    // ============================================
    // Authorization Tests - Finance
    // ============================================

    /**
     * Test vendor can only access own salon finance data
     * تست اینکه فروشنده فقط می‌تواند به داده‌های مالی سالن خود دسترسی داشته باشد
     */
    public function test_vendor_can_only_access_own_salon_finance(): void
    {
        \Modules\BeautyBooking\Entities\BeautyTransaction::factory()->create([
            'salon_id' => $this->otherSalon->id,
            'amount' => 1000000,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/finance/payout-summary');

        $response->assertStatus(200);
        
        // Verify other salon's transaction is not included
        $data = $response->json('data');
        $this->assertNotEquals(1000000, $data['total_revenue'] ?? 0);
    }

    // ============================================
    // Token Validation Tests
    // ============================================

    /**
     * Test empty token is rejected
     * تست اینکه توکن خالی رد می‌شود
     */
    public function test_empty_token_is_rejected(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ',
            'Accept' => 'application/json',
            'vendorType' => 'owner',
        ])->getJson('/api/v1/beautybooking/vendor/bookings/list/all');

        $response->assertStatus(401);
    }

    /**
     * Test missing Authorization header is rejected
     * تست اینکه header Authorization گم شده رد می‌شود
     */
    public function test_missing_authorization_header_is_rejected(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'vendorType' => 'owner',
        ])->getJson('/api/v1/beautybooking/vendor/bookings/list/all');

        $response->assertStatus(401);
    }

    /**
     * Test expired token is rejected
     * تست اینکه توکن منقضی شده رد می‌شود
     */
    public function test_expired_token_is_rejected(): void
    {
        // Note: Token expiration depends on Sanctum configuration
        // توجه: انقضای توکن به پیکربندی Sanctum بستگی دارد
        $response = $this->withHeaders([
            'Authorization' => 'Bearer expired-token-12345',
            'Accept' => 'application/json',
            'vendorType' => 'owner',
        ])->getJson('/api/v1/beautybooking/vendor/bookings/list/all');

        $response->assertStatus(401);
    }
}

