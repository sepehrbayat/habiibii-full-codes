<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\BeautyBooking\Services\BeautyRevenueService;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautySubscription;
use Modules\BeautyBooking\Entities\BeautyPackage;
use Modules\BeautyBooking\Entities\BeautyGiftCard;
use Modules\BeautyBooking\Entities\BeautyTransaction;
use App\Models\Store;

/**
 * Beauty Revenue Service Test
 * تست سرویس درآمد
 */
class BeautyRevenueServiceTest extends TestCase
{
    use RefreshDatabase;

    private BeautyRevenueService $revenueService;
    private BeautySalon $salon;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->revenueService = app(BeautyRevenueService::class);
        
        // Create test store and salon
        // ایجاد فروشگاه و سالن تست
        $store = Store::factory()->create();
        
        $this->salon = BeautySalon::factory()->create([
            'store_id' => $store->id,
        ]);
    }

    /**
     * Test commission recording
     * تست ثبت کمیسیون
     */
    public function test_record_commission(): void
    {
        $booking = BeautyBooking::factory()->create([
            'salon_id' => $this->salon->id,
            'total_amount' => 100000,
            'commission_amount' => 10000,
        ]);
        
        $this->revenueService->recordCommission($booking);
        
        $transaction = BeautyTransaction::where('booking_id', $booking->id)
            ->where('transaction_type', 'commission')
            ->first();
        
        $this->assertNotNull($transaction);
        $this->assertEquals(10000, $transaction->commission);
    }

    /**
     * Test subscription recording
     * تست ثبت اشتراک
     */
    public function test_record_subscription(): void
    {
        $subscription = BeautySubscription::factory()->create([
            'salon_id' => $this->salon->id,
            'amount_paid' => 50000,
        ]);
        
        $this->revenueService->recordSubscription($subscription);
        
        $transaction = BeautyTransaction::where('salon_id', $this->salon->id)
            ->where('transaction_type', 'subscription')
            ->first();
        
        $this->assertNotNull($transaction);
        $this->assertEquals(50000, $transaction->amount);
    }

    /**
     * Test service fee recording
     * تست ثبت هزینه سرویس
     */
    public function test_record_service_fee(): void
    {
        $booking = BeautyBooking::factory()->create([
            'salon_id' => $this->salon->id,
            'total_amount' => 100000,
            'service_fee' => 2000,
        ]);
        
        $this->revenueService->recordServiceFee($booking);
        
        $transaction = BeautyTransaction::where('booking_id', $booking->id)
            ->where('transaction_type', 'service_fee')
            ->first();
        
        $this->assertNotNull($transaction);
        $this->assertEquals(2000, $transaction->service_fee);
        $this->assertEquals(100000, $transaction->amount);
    }

    /**
     * Test cancellation fee recording
     * تست ثبت هزینه لغو
     */
    public function test_record_cancellation_fee(): void
    {
        $booking = BeautyBooking::factory()->create([
            'salon_id' => $this->salon->id,
            'total_amount' => 100000,
        ]);
        
        $feeAmount = 50000;
        $this->revenueService->recordCancellationFee($booking, $feeAmount);
        
        $transaction = BeautyTransaction::where('booking_id', $booking->id)
            ->where('transaction_type', 'cancellation_fee')
            ->first();
        
        $this->assertNotNull($transaction);
        $this->assertEquals($feeAmount, $transaction->amount);
    }

    /**
     * Test package sale recording
     * تست ثبت فروش پکیج
     */
    public function test_record_package_sale(): void
    {
        $package = BeautyPackage::factory()->create([
            'salon_id' => $this->salon->id,
            'total_price' => 200000,
        ]);
        
        $booking = BeautyBooking::factory()->create([
            'salon_id' => $this->salon->id,
        ]);
        
        $this->revenueService->recordPackageSale($package, $booking);
        
        $transaction = BeautyTransaction::where('salon_id', $this->salon->id)
            ->where('transaction_type', 'package_sale')
            ->first();
        
        $this->assertNotNull($transaction);
        $this->assertEquals(200000, $transaction->amount);
    }

    /**
     * Test gift card sale recording
     * تست ثبت فروش کارت هدیه
     */
    public function test_record_gift_card_sale(): void
    {
        $giftCard = BeautyGiftCard::factory()->create([
            'salon_id' => $this->salon->id,
            'amount' => 50000,
        ]);
        
        $this->revenueService->recordGiftCardSale($giftCard);
        
        $transaction = BeautyTransaction::where('salon_id', $this->salon->id)
            ->where('transaction_type', 'gift_card_sale')
            ->first();
        
        $this->assertNotNull($transaction);
        $this->assertEquals(50000, $transaction->amount);
    }

    /**
     * Test duplicate prevention
     * تست جلوگیری از تکرار
     */
    public function test_duplicate_prevention(): void
    {
        $booking = BeautyBooking::factory()->create([
            'salon_id' => $this->salon->id,
            'total_amount' => 100000,
            'commission_amount' => 10000,
        ]);
        
        // Record commission twice
        // ثبت کمیسیون دو بار
        $this->revenueService->recordCommission($booking);
        $this->revenueService->recordCommission($booking);
        
        $transactions = BeautyTransaction::where('booking_id', $booking->id)
            ->where('transaction_type', 'commission')
            ->count();
        
        // Should only have one transaction
        // باید فقط یک تراکنش داشته باشد
        $this->assertEquals(1, $transactions);
    }
}

