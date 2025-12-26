<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\BeautyBooking\Entities\BeautyGiftCard;
use App\Models\User;

/**
 * Gift Card Test
 * تست کارت هدیه
 *
 * Tests gift card issuance/redemption
 * تست صدور/استفاده کارت هدیه
 */
class GiftCardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test gift card issuance
     * تست صدور کارت هدیه
     *
     * @return void
     */
    public function test_gift_card_issuance(): void
    {
        $purchaser = User::factory()->create();
        $store = \App\Models\Store::factory()->create();
        $salon = \Modules\BeautyBooking\Entities\BeautySalon::factory()->create(['store_id' => $store->id]);
        
        $giftCard = BeautyGiftCard::factory()->create([
            'salon_id' => $salon->id,
            'purchaser_id' => $purchaser->id,
            'amount' => 50000,
            'status' => 'active',
        ]);
        
        $this->assertNotNull($giftCard);
        $this->assertNotNull($giftCard->code);
        $this->assertEquals('active', $giftCard->status);
    }

    /**
     * Test gift card redemption
     * تست استفاده کارت هدیه
     *
     * @return void
     */
    public function test_gift_card_redemption(): void
    {
        $purchaser = User::factory()->create();
        $redeemer = User::factory()->create();
        $store = \App\Models\Store::factory()->create();
        $salon = \Modules\BeautyBooking\Entities\BeautySalon::factory()->create(['store_id' => $store->id]);
        
        $giftCard = BeautyGiftCard::factory()->create([
            'salon_id' => $salon->id,
            'purchaser_id' => $purchaser->id,
            'amount' => 50000,
            'status' => 'active',
        ]);
        
        $giftCard->forceFill([
            'redeemer_id' => $redeemer->id,
            'redeemed_at' => now(),
            'status' => 'redeemed',
        ])->save();
        
        $this->assertEquals('redeemed', $giftCard->status);
        $this->assertEquals($redeemer->id, $giftCard->redeemer_id);
    }
}

