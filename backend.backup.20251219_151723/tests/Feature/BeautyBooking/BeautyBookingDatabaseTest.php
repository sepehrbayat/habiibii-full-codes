<?php

declare(strict_types=1);

namespace Tests\Feature\BeautyBooking;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\BeautyBooking\Database\Seeders\BeautyBookingDatabaseSeeder;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyServiceCategory;
use Tests\TestCase;

/**
 * تست‌های پایگاه‌داده برای ماژول رزرو زیبایی
 * Database tests for the Beauty Booking module
 */
class BeautyBookingDatabaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ایجاد رزرو با factory و بررسی ذخیره‌سازی
     * Create a booking with the factory and verify it is persisted
     *
     * @return void
     */
    public function test_booking_factory_creates_record(): void
    {
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
    }

    /**
     * حذف نرم رزرو باید در پایگاه‌داده ثبت شود
     * Soft deleting a booking should be reflected in the database
     *
     * @return void
     */
    public function test_soft_delete_marks_booking(): void
    {
        $booking = BeautyBooking::factory()->create();

        $booking->delete();

        $this->assertSoftDeleted($booking);
        $this->assertDatabaseMissing('beauty_bookings', [
            'id' => $booking->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * اجرای seeder باید دسته‌بندی‌های پیش‌فرض را بسازد
     * Running the seeder should create default beauty categories
     *
     * @return void
     */
    public function test_default_beauty_seeder_populates_categories(): void
    {
        $this->seed(BeautyBookingDatabaseSeeder::class);

        $this->assertDatabaseHas('beauty_service_categories', [
            'name' => 'Hair Services',
        ]);

        $this->assertTrue(BeautyServiceCategory::count() > 0);
    }

    /**
     * اطمینان از ایجاد سالن با factory
     * Ensure salon creation via factory
     *
     * @return void
     */
    public function test_salon_factory_creates_record(): void
    {
        $salon = \Modules\BeautyBooking\Entities\BeautySalon::factory()->create();

        $this->assertDatabaseHas('beauty_salons', [
            'id' => $salon->id,
            'store_id' => $salon->store_id,
        ]);
    }

    /**
     * اطمینان از ایجاد خدمت و ارتباط با سالن
     * Ensure service creation and linkage to salon
     *
     * @return void
     */
    public function test_service_factory_creates_record(): void
    {
        $service = \Modules\BeautyBooking\Entities\BeautyService::factory()->create();

        $this->assertDatabaseHas('beauty_services', [
            'id' => $service->id,
            'salon_id' => $service->salon_id,
            'category_id' => $service->category_id,
        ]);
    }

    /**
     * اطمینان از ایجاد کارمند برای سالن
     * Ensure staff creation for a salon
     *
     * @return void
     */
    public function test_staff_factory_creates_record(): void
    {
        $staff = \Modules\BeautyBooking\Entities\BeautyStaff::factory()->create();

        $this->assertDatabaseHas('beauty_staff', [
            'id' => $staff->id,
            'salon_id' => $staff->salon_id,
        ]);
    }

    /**
     * اطمینان از ایجاد بلاک تقویم
     * Ensure calendar block creation
     *
     * @return void
     */
    public function test_calendar_block_factory_creates_record(): void
    {
        $block = \Modules\BeautyBooking\Entities\BeautyCalendarBlock::factory()->create();

        $this->assertDatabaseHas('beauty_calendar_blocks', [
            'id' => $block->id,
            'salon_id' => $block->salon_id,
        ]);
    }

    /**
     * اطمینان از ایجاد محصول خرده‌فروشی
     * Ensure retail product creation
     *
     * @return void
     */
    public function test_retail_product_factory_creates_record(): void
    {
        $product = \Modules\BeautyBooking\Entities\BeautyRetailProduct::factory()->create();

        $this->assertDatabaseHas('beauty_retail_products', [
            'id' => $product->id,
            'salon_id' => $product->salon_id,
            'name' => $product->name,
        ]);
    }

    /**
     * اطمینان از ایجاد سفارش خرده‌فروشی
     * Ensure retail order creation
     *
     * @return void
     */
    public function test_retail_order_factory_creates_record(): void
    {
        $order = \Modules\BeautyBooking\Entities\BeautyRetailOrder::factory()->create();

        $this->assertDatabaseHas('beauty_retail_orders', [
            'id' => $order->id,
            'salon_id' => $order->salon_id,
            'user_id' => $order->user_id,
            'order_reference' => $order->order_reference,
        ]);
    }

    /**
     * ایجاد رکوردهای پکیج، کارت هدیه، وفاداری و اشتراک
     * Create package, gift card, loyalty campaign, and subscription records
     *
     * @return void
     */
    public function test_creates_packages_giftcards_loyalty_subscriptions(): void
    {
        $salon = \Modules\BeautyBooking\Entities\BeautySalon::factory()->create();
        $service = \Modules\BeautyBooking\Entities\BeautyService::factory()->create([
            'salon_id' => $salon->id,
        ]);
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
            'code' => 'GC-' . Str::upper(Str::random(8)),
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
    }
}

