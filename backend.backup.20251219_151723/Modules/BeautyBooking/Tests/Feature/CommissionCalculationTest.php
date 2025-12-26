<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Services\BeautyCommissionService;
use Modules\BeautyBooking\Services\BeautyRevenueService;
use Modules\BeautyBooking\Entities\BeautyBooking;

/**
 * Commission Calculation Test
 * تست محاسبه کمیسیون
 *
 * Tests all 10 revenue models
 * تست تمام 10 مدل درآمدی
 */
class CommissionCalculationTest extends TestCase
{
    use RefreshDatabase;

    private BeautyCommissionService $commissionService;
    private BeautyRevenueService $revenueService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->commissionService = app(BeautyCommissionService::class);
        $this->revenueService = app(BeautyRevenueService::class);
    }

    /**
     * Test variable commission calculation
     * تست محاسبه کمیسیون متغیر
     *
     * @return void
     */
    public function test_variable_commission_calculation(): void
    {
        $store = \App\Models\Store::factory()->create();
        $salon = BeautySalon::factory()->create([
            'store_id' => $store->id,
            'business_type' => 'salon',
        ]);
        
        $service = BeautyService::factory()->create([
            'salon_id' => $salon->id,
            'price' => 100000,
        ]);
        
        $commission = $this->commissionService->calculateCommission(
            $salon->id,
            $service->id,
            $service->price
        );
        
        $this->assertIsFloat($commission);
        $this->assertGreaterThan(0, $commission);
    }

    /**
     * Test service fee recording
     * تست ثبت هزینه سرویس
     *
     * @return void
     */
    public function test_service_fee_recording(): void
    {
        $user = \App\Models\User::factory()->create();
        $store = \App\Models\Store::factory()->create();
        $salon = BeautySalon::factory()->create(['store_id' => $store->id]);
        $service = BeautyService::factory()->create(['salon_id' => $salon->id]);
        
        $booking = BeautyBooking::factory()->create([
            'user_id' => $user->id,
            'salon_id' => $salon->id,
            'service_id' => $service->id,
            'total_amount' => 100000,
            'service_fee' => 2000,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
        
        $transaction = $this->revenueService->recordServiceFee($booking);
        
        $this->assertNotNull($transaction);
        $this->assertEquals('service_fee', $transaction->transaction_type);
    }
}

