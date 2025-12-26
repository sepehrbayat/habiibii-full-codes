<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Feature\Api\Contract;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Illuminate\Support\Facades\Hash;
use Modules\BeautyBooking\Services\BeautyCalendarService;
use Modules\BeautyBooking\Entities\BeautyCalendarBlock;
use Mockery;
use Illuminate\Support\Facades\Route;

/**
 * API Contract Test
 * تست قرارداد API
 *
 * Ensures API responses match expected structure
 * اطمینان از تطابق ساختار پاسخ‌های API با انتظارات
 */
class BeautyBookingApiContractTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Load module API routes explicitly for tests
        Route::middleware('api')->group(module_path('BeautyBooking', 'Routes/api/v1/customer/api.php'));
        
        // Mock calendar service to always allow slots in tests
        $calendarMock = Mockery::mock(BeautyCalendarService::class);
        $calendarMock->shouldReceive('isTimeSlotAvailable')->andReturn(true);
        $calendarMock->shouldReceive('blockTimeSlot')->andReturn(new BeautyCalendarBlock());
        $calendarMock->shouldReceive('unblockTimeSlotForBooking')->andReturnTrue();
        app()->instance(BeautyCalendarService::class, $calendarMock);
        
        $this->user = User::factory()->create([
            'email' => 'contract@example.com',
            'password' => Hash::make('password'),
        ]);
        
        $this->token = $this->user->createToken('test-token')->accessToken;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test booking creation response structure
     * تست ساختار پاسخ ایجاد رزرو
     *
     * @return void
     */
    public function test_booking_creation_response_structure(): void
    {
        $salon = BeautySalon::factory()->create();
        $service = BeautyService::factory()->create(['salon_id' => $salon->id]);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/beautybooking/bookings', [
            'salon_id' => $salon->id,
            'service_id' => $service->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
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
                    'total_amount',
                    'booking_date',
                    'booking_time',
                ],
            ]);
    }

    /**
     * Test pagination structure
     * تست ساختار صفحه‌بندی
     *
     * @return void
     */
    public function test_pagination_structure(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/beautybooking/bookings');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'total',
                'per_page',
                'current_page',
                'last_page',
            ]);
    }

    /**
     * Test error response structure
     * تست ساختار پاسخ خطا
     *
     * @return void
     */
    public function test_error_response_structure(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/beautybooking/bookings', []);
        
        $response->assertStatus(422)
            ->assertJsonStructure([
                'errors',
            ]);
    }
}

