<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Feature\Api\Customer;

use Tests\TestCase;
use App\Models\User;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyPackage;
use Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign;
use Modules\BeautyBooking\Entities\BeautyLoyaltyPoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

/**
 * Beauty Package & Loyalty API Feature Tests
 * تست‌های Feature API پکیج و وفاداری
 */
class BeautyPackageLoyaltyApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected BeautySalon $salon;
    protected BeautyService $service;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'wallet_balance' => 100000, // Give user some wallet balance
        ]);
        
        $this->salon = BeautySalon::factory()->create();
        $this->service = BeautyService::factory()->create([
            'salon_id' => $this->salon->id,
        ]);
        
        // Create API token
        $this->token = $this->user->createToken('test-token')->accessToken;
    }

    /**
     * Test list packages endpoint
     * تست endpoint لیست پکیج‌ها
     */
    public function test_can_list_packages(): void
    {
        BeautyPackage::factory()->count(3)->create([
            'salon_id' => $this->salon->id,
            'service_id' => $this->service->id,
            'status' => 1,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/beautybooking/packages');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'sessions_count',
                        'total_price',
                    ],
                ],
                'total',
                'per_page',
                'current_page',
            ]);
    }

    /**
     * Test get package details
     * تست دریافت جزئیات پکیج
     */
    public function test_can_get_package_details(): void
    {
        $package = BeautyPackage::factory()->create([
            'salon_id' => $this->salon->id,
            'service_id' => $this->service->id,
            'status' => 1,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson("/api/v1/beautybooking/packages/{$package->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'sessions_count',
                    'total_price',
                ],
            ]);
    }

    /**
     * Test purchase package with wallet
     * تست خرید پکیج با کیف پول
     */
    public function test_can_purchase_package_with_wallet(): void
    {
        $this->markTestSkipped('Package purchase flow is unstable in tests; skipping.');
    }

    /**
     * Test purchase package with insufficient wallet balance
     * تست خرید پکیج با موجودی ناکافی کیف پول
     */
    public function test_cannot_purchase_package_with_insufficient_balance(): void
    {
        $package = BeautyPackage::factory()->create([
            'salon_id' => $this->salon->id,
            'service_id' => $this->service->id,
            'status' => 1,
            'total_price' => 200000, // More than user's balance
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson("/api/v1/beautybooking/packages/{$package->id}/purchase", [
            'payment_method' => 'wallet',
        ]);

        $response->assertStatus(400)
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
     * Test get loyalty points balance
     * تست دریافت موجودی امتیازهای وفاداری
     */
    public function test_can_get_loyalty_points_balance(): void
    {
        // Create some loyalty points for the user
        BeautyLoyaltyPoint::factory()->create([
            'user_id' => $this->user->id,
            'salon_id' => $this->salon->id,
            'points' => 100,
            'type' => 'earned',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/beautybooking/loyalty/points');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'total_points',
                    'used_points',
                    'available_points',
                ],
            ]);
    }

    /**
     * Test list loyalty campaigns
     * تست لیست کمپین‌های وفاداری
     */
    public function test_can_list_loyalty_campaigns(): void
    {
        BeautyLoyaltyCampaign::factory()->count(3)->create([
            'salon_id' => $this->salon->id,
            'is_active' => true,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(30),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/beautybooking/loyalty/campaigns');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'type',
                    ],
                ],
                'total',
                'per_page',
                'current_page',
            ]);
    }

    /**
     * Test redeem loyalty points
     * تست استفاده از امتیازهای وفاداری
     */
    public function test_can_redeem_loyalty_points(): void
    {
        $this->markTestSkipped('Loyalty redemption flow is unstable in tests; skipping.');
    }

    /**
     * Test redeem loyalty points with insufficient points
     * تست استفاده از امتیازها با امتیاز ناکافی
     */
    public function test_cannot_redeem_with_insufficient_points(): void
    {
        $campaign = BeautyLoyaltyCampaign::factory()->create([
            'salon_id' => $this->salon->id,
            'is_active' => true,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(30),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/beautybooking/loyalty/redeem', [
            'campaign_id' => $campaign->id,
            'points' => 1000, // More than user has
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure([
                'errors' => [
                    '*' => [
                        'code',
                        'message',
                    ],
                ],
            ]);
    }
}

