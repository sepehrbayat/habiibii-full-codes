<?php

declare(strict_types=1);

use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyServiceCategory;

it('creates bookings via factory and persists database rows', function () {
    $booking = BeautyBooking::factory()->create([
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);

    $this->assertDatabaseHas('beauty_bookings', [
        'id' => $booking->id,
        'booking_reference' => $booking->booking_reference,
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);
});

it('soft deletes bookings', function () {
    $booking = BeautyBooking::factory()->create();

    $booking->delete();

    $this->assertSoftDeleted($booking);
    $this->assertDatabaseMissing('beauty_bookings', [
        'id' => $booking->id,
        'deleted_at' => null,
    ]);
});

it('loads default beauty categories from seeders', function () {
    $this->assertDatabaseHas('beauty_service_categories', [
        'name' => 'Hair Services',
    ]);

    $this->assertTrue(BeautyServiceCategory::count() > 0);
});

it('creates salons via factory', function () {
    $salon = \Modules\BeautyBooking\Entities\BeautySalon::factory()->create();

    $this->assertDatabaseHas('beauty_salons', [
        'id' => $salon->id,
        'store_id' => $salon->store_id,
    ]);
});

it('creates services via factory and links to salon/category', function () {
    $service = \Modules\BeautyBooking\Entities\BeautyService::factory()->create();

    $this->assertDatabaseHas('beauty_services', [
        'id' => $service->id,
        'salon_id' => $service->salon_id,
        'category_id' => $service->category_id,
    ]);
});

it('creates staff via factory', function () {
    $staff = \Modules\BeautyBooking\Entities\BeautyStaff::factory()->create();

    $this->assertDatabaseHas('beauty_staff', [
        'id' => $staff->id,
        'salon_id' => $staff->salon_id,
    ]);
});

it('creates calendar blocks via factory', function () {
    $block = \Modules\BeautyBooking\Entities\BeautyCalendarBlock::factory()->create();

    $this->assertDatabaseHas('beauty_calendar_blocks', [
        'id' => $block->id,
        'salon_id' => $block->salon_id,
    ]);
});

it('creates retail products via factory', function () {
    $product = \Modules\BeautyBooking\Entities\BeautyRetailProduct::factory()->create();

    $this->assertDatabaseHas('beauty_retail_products', [
        'id' => $product->id,
        'salon_id' => $product->salon_id,
        'name' => $product->name,
    ]);
});

it('creates retail orders via factory', function () {
    $order = \Modules\BeautyBooking\Entities\BeautyRetailOrder::factory()->create();

    $this->assertDatabaseHas('beauty_retail_orders', [
        'id' => $order->id,
        'salon_id' => $order->salon_id,
        'user_id' => $order->user_id,
        'order_reference' => $order->order_reference,
    ]);
});

it('creates package, gift card, loyalty, subscription via models', function () {
    $salon = \Modules\BeautyBooking\Entities\BeautySalon::factory()->create();
    $service = \Modules\BeautyBooking\Entities\BeautyService::factory()->create(['salon_id' => $salon->id]);
    $user = \App\Models\User::factory()->create();

    $package = \Modules\BeautyBooking\Entities\BeautyPackage::create([
        'salon_id' => $salon->id,
        'service_id' => $service->id,
        'name' => 'Smoke Package',
        'sessions_count' => 5,
        'total_price' => 500000,
        'discount_percentage' => 10,
        'validity_days' => 90,
        'status' => true,
    ]);

    $giftCard = \Modules\BeautyBooking\Entities\BeautyGiftCard::create([
        'code' => 'GC-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8)),
        'salon_id' => $salon->id,
        'purchased_by' => $user->id,
        'redeemed_by' => null,
        'amount' => 150000,
        'status' => 'active',
        'expires_at' => now()->addMonths(6)->toDateString(),
        'redeemed_at' => null,
    ]);

    $subscription = \Modules\BeautyBooking\Entities\BeautySubscription::create([
        'salon_id' => $salon->id,
        'subscription_type' => 'featured_listing',
        'ad_position' => null,
        'banner_image' => null,
        'duration_days' => 30,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(30)->toDateString(),
        'amount_paid' => 200000,
        'payment_method' => 'cash',
        'auto_renew' => false,
        'status' => 'active',
    ]);

    $campaign = \Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign::create([
        'salon_id' => $salon->id,
        'name' => 'Smoke Loyalty',
        'description' => 'Test loyalty campaign',
        'type' => 'points',
        'rules' => ['points_per_booking' => 5],
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(3)->toDateString(),
        'is_active' => true,
        'commission_percentage' => 0,
        'commission_type' => 'percentage',
        'total_participants' => 0,
        'total_redeemed' => 0,
        'total_revenue' => 0,
    ]);

    $this->assertDatabaseHas('beauty_packages', ['id' => $package->id]);
    $this->assertDatabaseHas('beauty_gift_cards', ['id' => $giftCard->id]);
    $this->assertDatabaseHas('beauty_subscriptions', ['id' => $subscription->id]);
    $this->assertDatabaseHas('beauty_loyalty_campaigns', ['id' => $campaign->id]);
});

