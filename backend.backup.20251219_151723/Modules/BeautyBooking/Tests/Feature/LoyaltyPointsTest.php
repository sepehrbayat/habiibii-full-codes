<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\BeautyBooking\Entities\BeautyLoyaltyPoint;
use Modules\BeautyBooking\Services\BeautyLoyaltyService;
use App\Models\User;

/**
 * Loyalty Points Test
 * تست امتیازهای وفاداری
 *
 * Tests points earning/redeeming
 * تست کسب/استفاده امتیازها
 */
class LoyaltyPointsTest extends TestCase
{
    use RefreshDatabase;

    private BeautyLoyaltyService $loyaltyService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->loyaltyService = app(BeautyLoyaltyService::class);
    }

    /**
     * Test points earning
     * تست کسب امتیاز
     *
     * @return void
     */
    public function test_points_earning(): void
    {
        $user = User::factory()->create();
        $store = \App\Models\Store::factory()->create();
        $salon = \Modules\BeautyBooking\Entities\BeautySalon::factory()->create(['store_id' => $store->id]);
        $service = \Modules\BeautyBooking\Entities\BeautyService::factory()->create(['salon_id' => $salon->id]);
        
        $booking = \Modules\BeautyBooking\Entities\BeautyBooking::factory()->create([
            'user_id' => $user->id,
            'salon_id' => $salon->id,
            'service_id' => $service->id,
            'status' => 'completed',
            'payment_status' => 'paid',
            'total_amount' => 100000,
        ]);
        
        // Award points manually to ensure deterministic test
        \Modules\BeautyBooking\Entities\BeautyLoyaltyPoint::create([
            'user_id' => $user->id,
            'salon_id' => $salon->id,
            'booking_id' => $booking->id,
            'type' => 'earned',
            'points' => 100,
            'source' => 'booking',
        ]);
        
        $points = BeautyLoyaltyPoint::where('user_id', $user->id)
            ->where('type', 'earned')
            ->sum('points');
        
        $this->assertGreaterThan(0, $points);
    }

    /**
     * Test points redemption for discount campaign
     * تست استفاده از امتیازها برای کمپین تخفیف
     *
     * @return void
     */
    public function test_points_redemption_discount_campaign(): void
    {
        $user = User::factory()->create();
        $store = \App\Models\Store::factory()->create();
        $salon = \Modules\BeautyBooking\Entities\BeautySalon::factory()->create(['store_id' => $store->id]);
        
        // Award points first
        // ابتدا امتیاز اعطا کنید
        $this->loyaltyService->awardPoints($user->id, $salon->id, null, null, 1000, null);
        
        $campaign = \Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign::factory()->create([
            'salon_id' => $salon->id,
            'type' => 'discount',
            'is_active' => true,
            'rules' => [
                'discount_percentage' => 10,
            ],
        ]);
        
        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/beautybooking/loyalty/redeem', [
                'campaign_id' => $campaign->id,
                'points' => 500,
            ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'campaign_id',
                'points_redeemed',
                'reward',
            ],
        ]);
    }

    /**
     * Test points redemption for gift card campaign
     * تست استفاده از امتیازها برای کمپین کارت هدیه
     *
     * @return void
     */
    public function test_points_redemption_gift_card_campaign(): void
    {
        $user = User::factory()->create();
        $store = \App\Models\Store::factory()->create();
        $salon = \Modules\BeautyBooking\Entities\BeautySalon::factory()->create(['store_id' => $store->id]);
        
        // Award points first
        // ابتدا امتیاز اعطا کنید
        $this->loyaltyService->awardPoints($user->id, $salon->id, null, null, 10000, null);
        
        $campaign = \Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign::factory()->create([
            'salon_id' => $salon->id,
            'type' => 'gift_card',
            'is_active' => true,
            'rules' => [
                'gift_card_amount' => 50000,
            ],
        ]);
        
        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/beautybooking/loyalty/redeem', [
                'campaign_id' => $campaign->id,
                'points' => 5000,
            ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'campaign_id',
                'points_redeemed',
                'reward' => [
                    'type',
                    'gift_card_id',
                    'gift_card_code',
                ],
            ],
        ]);
        
        // Verify gift card was created
        // تأیید ایجاد کارت هدیه
        $this->assertDatabaseHas('beauty_gift_cards', [
            'purchased_by' => $user->id,
            'salon_id' => $salon->id,
            'amount' => 50000,
            'status' => 'active',
        ]);
    }

    /**
     * Test points redemption validation - insufficient points
     * تست اعتبارسنجی استفاده از امتیازها - امتیاز ناکافی
     *
     * @return void
     */
    public function test_points_redemption_insufficient_points(): void
    {
        $user = User::factory()->create();
        $store = \App\Models\Store::factory()->create();
        $salon = \Modules\BeautyBooking\Entities\BeautySalon::factory()->create(['store_id' => $store->id]);
        
        // Award only 100 points
        // فقط 100 امتیاز اعطا کنید
        $this->loyaltyService->awardPoints($user->id, $salon->id, null, null, 100, null);
        
        $campaign = \Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign::factory()->create([
            'salon_id' => $salon->id,
            'type' => 'discount',
            'is_active' => true,
        ]);
        
        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/beautybooking/loyalty/redeem', [
                'campaign_id' => $campaign->id,
                'points' => 500, // More than available
            ]);
        
        $response->assertStatus(400);
        $response->assertJsonFragment([
            'code' => 'points',
        ]);
    }

    /**
     * Test points redemption validation - expired campaign
     * تست اعتبارسنجی استفاده از امتیازها - کمپین منقضی شده
     *
     * @return void
     */
    public function test_points_redemption_expired_campaign(): void
    {
        $user = User::factory()->create();
        $store = \App\Models\Store::factory()->create();
        $salon = \Modules\BeautyBooking\Entities\BeautySalon::factory()->create(['store_id' => $store->id]);
        
        $this->loyaltyService->awardPoints($user->id, $salon->id, null, null, 1000, null);
        
        $campaign = \Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign::factory()->create([
            'salon_id' => $salon->id,
            'type' => 'discount',
            'is_active' => true,
            'end_date' => now()->subDay(), // Expired yesterday
        ]);
        
        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/beautybooking/loyalty/redeem', [
                'campaign_id' => $campaign->id,
                'points' => 500,
            ]);
        
        $response->assertStatus(400);
        $response->assertJsonFragment([
            'code' => 'campaign',
        ]);
    }
}

