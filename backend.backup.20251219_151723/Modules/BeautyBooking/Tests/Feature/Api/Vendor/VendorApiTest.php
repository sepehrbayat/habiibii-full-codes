<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Feature\Api\Vendor;

use Tests\TestCase;
use App\Models\Vendor;
use App\Models\Store;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyStaff;
use Modules\BeautyBooking\Entities\BeautyServiceCategory;
use Modules\BeautyBooking\Entities\BeautyRetailProduct;
use Modules\BeautyBooking\Entities\BeautyRetailOrder;
use Modules\BeautyBooking\Entities\BeautySubscription;
use Modules\BeautyBooking\Entities\BeautyPackage;
use Modules\BeautyBooking\Entities\BeautyGiftCard;
use Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign;
use Modules\BeautyBooking\Entities\BeautyLoyaltyPoint;
use Modules\BeautyBooking\Entities\BeautyTransaction;
use Modules\BeautyBooking\Entities\BeautyBadge;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Modules\BeautyBooking\Entities\BeautyCalendarBlock;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Mockery;
use Illuminate\Support\Facades\DB as FacadesDB;

/**
 * Comprehensive Vendor API Feature Tests
 * تست‌های جامع Feature API برای فروشنده
 *
 * Tests all 43 vendor API endpoints
 * تست تمام 43 endpoint API فروشنده
 */
class VendorApiTest extends TestCase
{
    use DatabaseTransactions;

    protected Vendor $vendor;
    protected Store $store;
    protected BeautySalon $salon;
    protected BeautyService $service;
    protected BeautyServiceCategory $category;
    protected BeautyStaff $staff;
    protected BeautyBooking $booking;
    protected string $token;

    /**
     * Ensure vendor store and salon linkage exists
     * اطمینان از وجود ارتباط فروشنده، فروشگاه و سالن
     */
    protected function ensureVendorSalon(): void
    {
        $this->vendor = $this->vendor->fresh();
        if (!$this->vendor->store_id) {
            $this->vendor->forceFill(['store_id' => $this->store->id])->save();
        }

        $this->salon = BeautySalon::firstOrCreate(
            ['store_id' => $this->store->id],
            [
                'zone_id' => $this->store->zone_id ?? null,
                'business_type' => 'salon',
                'verification_status' => 0,
                'is_verified' => false,
            ]
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // Load module vendor routes explicitly with correct prefix
        Route::prefix('api/v1/beautybooking/vendor')
            ->middleware('api')
            ->group(module_path('BeautyBooking', 'Routes/api/v1/vendor/api.php'));
        
        // Mock calendar service to always allow slots
        $calendarMock = Mockery::mock(BeautyCalendarService::class);
        $calendarMock->shouldReceive('isTimeSlotAvailable')->andReturn(true);
        $calendarMock->shouldReceive('blockTimeSlot')->andReturn(new BeautyCalendarBlock());
        $calendarMock->shouldReceive('unblockTimeSlotForBooking')->andReturnTrue();
        $calendarMock->shouldReceive('getAvailableTimeSlots')->andReturn([]);
        $calendarMock->shouldReceive('unblockTimeSlot')->andReturnTrue();
        app()->instance(BeautyCalendarService::class, $calendarMock);
        
        Storage::fake('public');
        Mail::fake();

        DB::table('business_settings')->updateOrInsert(
            ['key' => 'schedule_order'],
            ['value' => json_encode(['status' => 1])]
        );
        
        $this->vendor = Vendor::factory()->create([
            'email' => uniqid('vendor_main_', true) . '@example.com',
        ]);
        $this->store = Store::factory()->create([
            'vendor_id' => $this->vendor->id,
        ]);
        $this->vendor->forceFill(['store_id' => $this->store->id])->save();
        
        $this->salon = BeautySalon::factory()->create([
            'store_id' => $this->store->id,
        ]);
        
        $this->category = BeautyServiceCategory::factory()->create();
        
        $this->service = BeautyService::factory()->create([
            'salon_id' => $this->salon->id,
            'category_id' => $this->category->id,
        ]);
        
        $this->staff = BeautyStaff::factory()->create([
            'salon_id' => $this->salon->id,
        ]);
        
        $this->booking = BeautyBooking::factory()->create([
            'salon_id' => $this->salon->id,
            'service_id' => $this->service->id,
            'staff_id' => $this->staff->id,
            'status' => 'pending',
            'payment_method' => 'cash_payment',
            'user_id' => \App\Models\User::factory()->create([
                'email' => uniqid('vendor_booking_user_', true) . '@example.com',
            ])->id,
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

    protected function getAuthHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
            'vendorType' => 'owner',
        ];
    }

    // ============================================
    // Booking Management Tests
    // ============================================

    /**
     * Test vendor can list all bookings
     * تست اینکه فروشنده می‌تواند لیست تمام رزروها را ببیند
     */
    public function test_vendor_can_list_all_bookings(): void
    {
        BeautyBooking::factory()->count(5)->create([
            'salon_id' => $this->salon->id,
            'service_id' => $this->service->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/bookings/list/all');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'booking_reference',
                        'status',
                    ],
                ],
                'total',
                'per_page',
                'current_page',
                'last_page',
            ]);
    }

    /**
     * Test vendor can list bookings filtered by status
     * تست اینکه فروشنده می‌تواند رزروها را بر اساس وضعیت فیلتر کند
     */
    public function test_vendor_can_list_bookings_by_status(): void
    {
        BeautyBooking::factory()->create([
            'salon_id' => $this->salon->id,
            'service_id' => $this->service->id,
            'status' => 'confirmed',
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/bookings/list/confirmed');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.status', 'confirmed');
    }

    /**
     * Test vendor can get booking details
     * تست اینکه فروشنده می‌تواند جزئیات رزرو را دریافت کند
     */
    public function test_vendor_can_get_booking_details(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/bookings/details?booking_id=' . $this->booking->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'booking_reference',
                    'status',
                    'user',
                    'service',
                ],
            ]);
    }

    /**
     * Test vendor can confirm booking
     * تست اینکه فروشنده می‌تواند رزرو را تأیید کند
     */
    public function test_vendor_can_confirm_booking(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->putJson('/api/v1/beautybooking/vendor/bookings/confirm', [
                'booking_id' => $this->booking->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'confirmed');
        
        $this->assertDatabaseHas('beauty_bookings', [
            'id' => $this->booking->id,
            'status' => 'confirmed',
        ]);
    }

    /**
     * Test vendor can complete booking
     * تست اینکه فروشنده می‌تواند رزرو را تکمیل کند
     */
    public function test_vendor_can_complete_booking(): void
    {
        $completedAt = Carbon::now()->subHour();
        $this->booking->update([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'booking_date' => $completedAt->toDateString(),
            'booking_time' => $completedAt->format('H:i:s'),
            'booking_date_time' => $completedAt,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->putJson('/api/v1/beautybooking/vendor/bookings/complete', [
                'booking_id' => $this->booking->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'completed');
    }

    /**
     * Test vendor can mark payment as paid
     * تست اینکه فروشنده می‌تواند پرداخت را به عنوان پرداخت شده علامت بزند
     */
    public function test_vendor_can_mark_payment_as_paid(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->putJson('/api/v1/beautybooking/vendor/bookings/mark-paid', [
                'booking_id' => $this->booking->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.payment_status', 'paid');
    }

    /**
     * Test vendor can cancel booking
     * تست اینکه فروشنده می‌تواند رزرو را لغو کند
     */
    public function test_vendor_can_cancel_booking(): void
    {
        $this->booking->update(['status' => 'confirmed']);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->putJson('/api/v1/beautybooking/vendor/bookings/cancel', [
                'booking_id' => $this->booking->id,
                'cancellation_reason' => 'Salon closed',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');
    }

    // ============================================
    // Staff Management Tests
    // ============================================

    /**
     * Test vendor can list staff
     * تست اینکه فروشنده می‌تواند لیست کارمندان را ببیند
     */
    public function test_vendor_can_list_staff(): void
    {
        BeautyStaff::factory()->count(3)->create([
            'salon_id' => $this->salon->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/staff/list');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data',
                'total',
            ]);
    }

    /**
     * Test vendor can create staff
     * تست اینکه فروشنده می‌تواند کارمند ایجاد کند
     */
    public function test_vendor_can_create_staff(): void
    {
        $avatar = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson('/api/v1/beautybooking/vendor/staff/create', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+1234567890',
                'avatar' => $avatar,
                'status' => 1,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'John Doe');
        
        $this->assertDatabaseHas('beauty_staff', [
            'salon_id' => $this->salon->id,
            'name' => 'John Doe',
        ]);
    }

    /**
     * Test vendor can update staff
     * تست اینکه فروشنده می‌تواند کارمند را به‌روزرسانی کند
     */
    public function test_vendor_can_update_staff(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson("/api/v1/beautybooking/vendor/staff/update/{$this->staff->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');
    }

    /**
     * Test vendor can get staff details
     * تست اینکه فروشنده می‌تواند جزئیات کارمند را دریافت کند
     */
    public function test_vendor_can_get_staff_details(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson("/api/v1/beautybooking/vendor/staff/details/{$this->staff->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);
    }

    /**
     * Test vendor can delete staff
     * تست اینکه فروشنده می‌تواند کارمند را حذف کند
     */
    public function test_vendor_can_delete_staff(): void
    {
        $staff = BeautyStaff::factory()->create([
            'salon_id' => $this->salon->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->deleteJson("/api/v1/beautybooking/vendor/staff/delete/{$staff->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('beauty_staff', ['id' => $staff->id]);
    }

    /**
     * Test vendor can toggle staff status
     * تست اینکه فروشنده می‌تواند وضعیت کارمند را تغییر دهد
     */
    public function test_vendor_can_toggle_staff_status(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson("/api/v1/beautybooking/vendor/staff/status/{$this->staff->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'status',
                ],
            ]);
    }

    // ============================================
    // Service Management Tests
    // ============================================

    /**
     * Test vendor can list services
     * تست اینکه فروشنده می‌تواند لیست خدمات را ببیند
     */
    public function test_vendor_can_list_services(): void
    {
        BeautyService::factory()->count(3)->create([
            'salon_id' => $this->salon->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/service/list');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data',
                'total',
            ]);
    }

    /**
     * Test vendor can create service
     * تست اینکه فروشنده می‌تواند خدمت ایجاد کند
     */
    public function test_vendor_can_create_service(): void
    {
        $image = UploadedFile::fake()->image('service.jpg');

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson('/api/v1/beautybooking/vendor/service/create', [
                'category_id' => $this->category->id,
                'name' => 'New Service',
                'description' => 'Service description',
                'duration_minutes' => 60,
                'price' => 100000,
                'image' => $image,
                'status' => 1,
                'staff_ids' => [$this->staff->id],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Service');
    }

    /**
     * Test vendor can update service
     * تست اینکه فروشنده می‌تواند خدمت را به‌روزرسانی کند
     */
    public function test_vendor_can_update_service(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson("/api/v1/beautybooking/vendor/service/update/{$this->service->id}", [
                'name' => 'Updated Service',
                'price' => 150000,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Service');
    }

    /**
     * Test vendor can get service details
     * تست اینکه فروشنده می‌تواند جزئیات خدمت را دریافت کند
     */
    public function test_vendor_can_get_service_details(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson("/api/v1/beautybooking/vendor/service/details/{$this->service->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'category',
                ],
            ]);
    }

    /**
     * Test vendor can delete service
     * تست اینکه فروشنده می‌تواند خدمت را حذف کند
     */
    public function test_vendor_can_delete_service(): void
    {
        $service = BeautyService::factory()->create([
            'salon_id' => $this->salon->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->deleteJson("/api/v1/beautybooking/vendor/service/delete/{$service->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('beauty_services', ['id' => $service->id]);
    }

    /**
     * Test vendor can toggle service status
     * تست اینکه فروشنده می‌تواند وضعیت خدمت را تغییر دهد
     */
    public function test_vendor_can_toggle_service_status(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson("/api/v1/beautybooking/vendor/service/status/{$this->service->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'status',
                ],
            ]);
    }

    // ============================================
    // Calendar Management Tests
    // ============================================

    /**
     * Test vendor can get calendar availability
     * تست اینکه فروشنده می‌تواند دسترسی‌پذیری تقویم را دریافت کند
     */
    public function test_vendor_can_get_calendar_availability(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/calendar/availability?date=' . now()->addDay()->format('Y-m-d') . '&service_id=' . $this->service->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'date',
                    'available_slots',
                    'duration_minutes',
                ],
            ]);
    }

    /**
     * Test vendor can create calendar block
     * تست اینکه فروشنده می‌تواند بلاک تقویم ایجاد کند
     */
    public function test_vendor_can_create_calendar_block(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson('/api/v1/beautybooking/vendor/calendar/blocks/create', [
                'date' => now()->addDay()->format('Y-m-d'),
                'start_time' => '12:00',
                'end_time' => '13:00',
                'type' => 'break',
                'reason' => 'Lunch break',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data',
            ]);
    }

    /**
     * Test vendor can delete calendar block
     * تست اینکه فروشنده می‌تواند بلاک تقویم را حذف کند
     */
    public function test_vendor_can_delete_calendar_block(): void
    {
        $block = BeautyCalendarBlock::factory()->create([
            'salon_id' => $this->salon->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->deleteJson("/api/v1/beautybooking/vendor/calendar/blocks/delete/{$block->id}");

        $response->assertStatus(200);
    }

    // ============================================
    // Salon Registration & Profile Tests
    // ============================================

    /**
     * Test vendor can register salon
     * تست اینکه فروشنده می‌تواند سالن را ثبت‌نام کند
     */
    public function test_vendor_can_register_salon(): void
    {
        $newVendor = Vendor::factory()->create();
        $newStore = Store::factory()->create([
            'vendor_id' => $newVendor->id,
        ]);
        $token = Str::random(60);
        $newVendor->forceFill([
            'auth_token' => $token,
            'store_id' => $newStore->id,
        ])->save();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'vendorType' => 'owner',
        ])->postJson('/api/v1/beautybooking/vendor/salon/register', [
            'business_type' => 'salon',
            'license_number' => 'LIC123456',
            'license_expiry' => now()->addYear()->format('Y-m-d'),
            'working_hours' => [
                ['day' => 'monday', 'open' => '09:00', 'close' => '18:00'],
                ['day' => 'tuesday', 'open' => '09:00', 'close' => '18:00'],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.business_type', 'salon');
    }

    /**
     * Test vendor can upload documents
     * تست اینکه فروشنده می‌تواند مدارک را آپلود کند
     */
    public function test_vendor_can_upload_documents(): void
    {
        $documents = [
            UploadedFile::fake()->create('document1.pdf', 100),
            UploadedFile::fake()->create('document2.jpg', 100),
        ];

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson('/api/v1/beautybooking/vendor/salon/documents/upload', [
                'documents' => $documents,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'documents',
                    'uploaded_count',
                ],
            ]);
    }

    /**
     * Test vendor can update working hours
     * تست اینکه فروشنده می‌تواند ساعات کاری را به‌روزرسانی کند
     */
    public function test_vendor_can_update_working_hours(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson('/api/v1/beautybooking/vendor/salon/working-hours/update', [
                'working_hours' => [
                    ['day' => 'monday', 'open' => '10:00', 'close' => '19:00'],
                ],
            ]);

        $response->assertStatus(200);
    }

    /**
     * Test vendor can manage holidays
     * تست اینکه فروشنده می‌تواند تعطیلات را مدیریت کند
     */
    public function test_vendor_can_manage_holidays(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson('/api/v1/beautybooking/vendor/salon/holidays/manage', [
                'action' => 'add',
                'holidays' => [
                    now()->addDays(2)->toDateString(),
                    now()->addDays(3)->toDateString(),
                ],
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'holidays',
                    'total_count',
                ],
            ]);
    }

    /**
     * Test vendor can get profile
     * تست اینکه فروشنده می‌تواند پروفایل را دریافت کند
     */
    public function test_vendor_can_get_profile(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'salon',
                    'store',
                    'badges',
                ],
            ]);
    }

    /**
     * Test vendor can update profile
     * تست اینکه فروشنده می‌تواند پروفایل را به‌روزرسانی کند
     */
    public function test_vendor_can_update_profile(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson('/api/v1/beautybooking/vendor/profile/update', [
                'license_number' => 'UPDATED123',
            ]);

        $response->assertStatus(200);
    }

    // ============================================
    // Retail Management Tests
    // ============================================

    /**
     * Test vendor can list retail products
     * تست اینکه فروشنده می‌تواند لیست محصولات خرده‌فروشی را ببیند
     */
    public function test_vendor_can_list_retail_products(): void
    {
        BeautyRetailProduct::factory()->count(3)->create([
            'salon_id' => $this->salon->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/retail/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data',
                'total',
            ]);
    }

    /**
     * Test vendor can create retail product
     * تست اینکه فروشنده می‌تواند محصول خرده‌فروشی ایجاد کند
     */
    public function test_vendor_can_create_retail_product(): void
    {
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson('/api/v1/beautybooking/vendor/retail/products', [
                'name' => 'Shampoo',
                'description' => 'Premium shampoo',
                'price' => 50000,
                'image' => $image,
                'category' => 'Hair Care',
                'stock_quantity' => 100,
                'status' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Shampoo');
    }

    /**
     * Test vendor can list retail orders
     * تست اینکه فروشنده می‌تواند لیست سفارشات خرده‌فروشی را ببیند
     */
    public function test_vendor_can_list_retail_orders(): void
    {
        $product = BeautyRetailProduct::factory()->create([
            'salon_id' => $this->salon->id,
        ]);

        BeautyRetailOrder::factory()->count(3)->create([
            'salon_id' => $this->salon->id,
            'product_id' => $product->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/retail/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data',
                'total',
            ]);
    }

    // ============================================
    // Subscription Management Tests
    // ============================================

    /**
     * Test vendor can get subscription plans
     * تست اینکه فروشنده می‌تواند پلان‌های اشتراک را دریافت کند
     */
    public function test_vendor_can_get_subscription_plans(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/subscription/plans');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'plans',
                    'active_subscriptions',
                ],
            ]);
    }

    /**
     * Test vendor can purchase subscription with cash
     * تست اینکه فروشنده می‌تواند اشتراک را با نقد خریداری کند
     */
    public function test_vendor_can_purchase_subscription_cash(): void
    {
        $response = $this->withHeaders($this->getAuthHeaders())
            ->postJson('/api/v1/beautybooking/vendor/subscription/purchase', [
                'subscription_type' => 'featured_listing',
                'duration_days' => 30,
                'payment_method' => 'cash_payment',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'active');
    }

    /**
     * Test vendor can get subscription history
     * تست اینکه فروشنده می‌تواند تاریخچه اشتراک را دریافت کند
     */
    public function test_vendor_can_get_subscription_history(): void
    {
        BeautySubscription::factory()->count(2)->create([
            'salon_id' => $this->salon->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/subscription/history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data',
                'total',
            ]);
    }

    // ============================================
    // Finance & Reports Tests
    // ============================================

    /**
     * Test vendor can get payout summary
     * تست اینکه فروشنده می‌تواند خلاصه پرداخت را دریافت کند
     */
    public function test_vendor_can_get_payout_summary(): void
    {
        $this->ensureVendorSalon();

        BeautyTransaction::factory()->create([
            'salon_id' => $this->salon->id,
            'transaction_type' => 'commission',
            'amount' => 100000,
            'commission' => 10000,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/finance/payout-summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'total_revenue',
                    'total_commission',
                    'net_payout',
                ],
            ]);
    }

    /**
     * Test vendor can get transaction history
     * تست اینکه فروشنده می‌تواند تاریخچه تراکنش را دریافت کند
     */
    public function test_vendor_can_get_transaction_history(): void
    {
        $this->ensureVendorSalon();

        BeautyTransaction::factory()->count(3)->create([
            'salon_id' => $this->salon->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/finance/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data',
                'total',
            ]);
    }

    // ============================================
    // Badge Management Tests
    // ============================================

    /**
     * Test vendor can get badge status
     * تست اینکه فروشنده می‌تواند وضعیت نشان‌ها را دریافت کند
     */
    public function test_vendor_can_get_badge_status(): void
    {
        $this->ensureVendorSalon();

        BeautyBadge::factory()->create([
            'salon_id' => $this->salon->id,
            'badge_type' => 'top_rated',
            'status' => 1,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/badge/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'current_badges',
                    'criteria',
                ],
            ]);
    }

    // ============================================
    // Package Management Tests
    // ============================================

    /**
     * Test vendor can list packages
     * تست اینکه فروشنده می‌تواند لیست پکیج‌ها را ببیند
     */
    public function test_vendor_can_list_packages(): void
    {
        $this->ensureVendorSalon();

        BeautyPackage::factory()->count(3)->create([
            'salon_id' => $this->salon->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/packages/list');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data',
                'total',
            ]);
    }

    /**
     * Test vendor can get package usage stats
     * تست اینکه فروشنده می‌تواند آمار استفاده از پکیج را دریافت کند
     */
    public function test_vendor_can_get_package_usage_stats(): void
    {
        $this->ensureVendorSalon();

        $package = BeautyPackage::factory()->create([
            'salon_id' => $this->salon->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/packages/usage-stats?package_id=' . $package->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'package',
                    'total_redemptions',
                    'remaining_sessions',
                ],
            ]);
    }

    // ============================================
    // Gift Card Management Tests
    // ============================================

    /**
     * Test vendor can list gift cards
     * تست اینکه فروشنده می‌تواند لیست کارت‌های هدیه را ببیند
     */
    public function test_vendor_can_list_gift_cards(): void
    {
        $this->ensureVendorSalon();

        BeautyGiftCard::factory()->count(3)->create([
            'salon_id' => $this->salon->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/gift-cards/list');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data',
                'total',
            ]);
    }

    /**
     * Test vendor can get redemption history
     * تست اینکه فروشنده می‌تواند تاریخچه استفاده را دریافت کند
     */
    public function test_vendor_can_get_redemption_history(): void
    {
        $this->ensureVendorSalon();

        BeautyGiftCard::factory()->create([
            'salon_id' => $this->salon->id,
            'status' => 'redeemed',
            'redeemed_at' => now(),
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/gift-cards/redemption-history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data',
                'total',
            ]);
    }

    // ============================================
    // Loyalty Campaign Management Tests
    // ============================================

    /**
     * Test vendor can list loyalty campaigns
     * تست اینکه فروشنده می‌تواند لیست کمپین‌های وفاداری را ببیند
     */
    public function test_vendor_can_list_loyalty_campaigns(): void
    {
        $this->ensureVendorSalon();

        BeautyLoyaltyCampaign::factory()->count(3)->create([
            'salon_id' => $this->salon->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/loyalty/campaigns');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data',
                'total',
            ]);
    }

    /**
     * Test vendor can get points history
     * تست اینکه فروشنده می‌تواند تاریخچه امتیازها را دریافت کند
     */
    public function test_vendor_can_get_points_history(): void
    {
        $this->ensureVendorSalon();

        BeautyLoyaltyPoint::factory()->count(3)->create([
            'booking_id' => $this->booking->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson('/api/v1/beautybooking/vendor/loyalty/points-history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data',
                'total',
            ]);
    }

    /**
     * Test vendor can get campaign stats
     * تست اینکه فروشنده می‌تواند آمار کمپین را دریافت کند
     */
    public function test_vendor_can_get_campaign_stats(): void
    {
        $this->ensureVendorSalon();

        $campaign = BeautyLoyaltyCampaign::factory()->create([
            'salon_id' => $this->salon->id,
        ]);

        $response = $this->withHeaders($this->getAuthHeaders())
            ->getJson("/api/v1/beautybooking/vendor/loyalty/campaign/{$campaign->id}/stats");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'campaign',
                    'total_points_issued',
                    'total_points_redeemed',
                    'total_users',
                ],
            ]);
    }
}

