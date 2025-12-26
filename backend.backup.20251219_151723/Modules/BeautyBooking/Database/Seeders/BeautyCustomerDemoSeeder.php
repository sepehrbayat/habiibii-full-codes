<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Database\Seeders;

use App\Models\Store;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Vendor;
use App\Models\WalletTransaction;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyGiftCard;
use Modules\BeautyBooking\Entities\BeautyLoyaltyPoint;
use Modules\BeautyBooking\Entities\BeautyPackage;
use Modules\BeautyBooking\Entities\BeautyPackageUsage;
use Modules\BeautyBooking\Entities\BeautyReview;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyServiceCategory;
use Modules\BeautyBooking\Entities\BeautyStaff;

/**
 * Seed demo data for a customer to experience Beauty module dashboards
 * ایجاد داده‌ی دمو برای مشتری تا همه قابلیت‌های داشبورد زیبایی را تجربه کند
 */
class BeautyCustomerDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * اجرای سیدها
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('🌸 Seeding beauty customer demo data...');

        $zone = $this->ensureZone();
        $vendor = $this->ensureVendor();
        $moduleId = $this->getBeautyModuleId();

        $salon = $this->ensureSalon($zone, $vendor->id, $moduleId);
        $service = $this->ensureService($salon);
        $staff = $this->ensureStaff($salon);
        $customer = $this->ensureCustomer();

        $this->createBookings($salon, $service, $staff, $customer);
        $this->createReviews($customer);
        $package = $this->createPackage($salon, $service);
        $this->createPackageUsage($package, $customer);
        $this->createGiftCards($salon, $customer);
        $this->createLoyaltyPoints($customer);
        $this->createWalletTransactions($customer);
        $this->createNotifications($customer, $salon);

        $this->command->info('✅ Beauty customer demo data seeded for john@customer.com');
    }

    /**
     * Ensure a default zone exists.
     * اطمینان از وجود یک منطقه پیش‌فرض
     */
    private function ensureZone(): Zone
    {
        return Zone::firstOrCreate(
            ['name' => 'Default Zone'],
            [
                'display_name' => 'Default Zone',
                'coordinates' => null,
                'status' => 1,
                'cash_on_delivery' => 1,
                'digital_payment' => 1,
                'offline_payment' => 1,
            ]
        );
    }

    /**
     * Ensure a vendor exists.
     * اطمینان از وجود یک وندور
     */
    private function ensureVendor(): Vendor
    {
        return Vendor::firstOrCreate(
            ['email' => 'beauty-vendor@example.com'],
            [
                'f_name' => 'Beauty',
                'l_name' => 'Vendor',
                'phone' => '09120000000',
                'password' => bcrypt('12345678'),
                'status' => 1,
            ]
        );
    }

    /**
     * Get module id for beauty.
     * دریافت شناسه ماژول زیبایی
     */
    private function getBeautyModuleId(): int
    {
        return (int) DB::table('modules')
            ->whereIn('module_name', ['beauty', 'Beauty Booking'])
            ->value('id') ?: 2;
    }

    /**
     * Ensure a verified salon and store exist.
     * اطمینان از وجود سالن و فروشگاه تأیید شده
     */
    private function ensureSalon(Zone $zone, int $vendorId, int $moduleId): BeautySalon
    {
        $store = Store::firstOrCreate(
            ['phone' => '02112345678'],
            [
                'name' => 'Elite Beauty Demo',
                'email' => 'elite-demo@beauty.com',
                'logo' => 'def.png',
                'cover_photo' => 'def.png',
                'latitude' => 35.6892,
                'longitude' => 51.3890,
                'zone_id' => $zone->id,
                'module_id' => $moduleId,
                'status' => 1,
                'active' => 1,
                'vendor_id' => $vendorId,
            ]
        );

        return BeautySalon::firstOrCreate(
            ['store_id' => $store->id],
            [
                'store_id' => $store->id,
                'zone_id' => $zone->id,
                'business_type' => 'salon',
                'license_number' => 'LIC-DEMO-001',
                'license_expiry' => Carbon::now()->addYear(),
                'documents' => ['doc1.pdf'],
                'verification_status' => 1,
                'is_verified' => true,
                'is_featured' => true,
                'working_hours' => [
                    'monday' => ['open' => '09:00', 'close' => '18:00'],
                    'tuesday' => ['open' => '09:00', 'close' => '18:00'],
                    'wednesday' => ['open' => '09:00', 'close' => '18:00'],
                    'thursday' => ['open' => '09:00', 'close' => '18:00'],
                    'friday' => ['open' => '09:00', 'close' => '18:00'],
                    'saturday' => ['open' => '10:00', 'close' => '16:00'],
                    'sunday' => null,
                ],
                'holidays' => [],
                'avg_rating' => 4.7,
                'total_bookings' => 0,
                'total_reviews' => 0,
                'total_cancellations' => 0,
                'cancellation_rate' => 0.0,
            ]
        );
    }

    /**
     * Ensure at least one service exists.
     * اطمینان از وجود حداقل یک خدمت
     */
    private function ensureService(BeautySalon $salon): BeautyService
    {
        $category = BeautyServiceCategory::firstOrCreate(
            ['name' => 'Hair'],
            ['status' => 1]
        );

        return BeautyService::firstOrCreate(
            ['salon_id' => $salon->id, 'name' => 'Haircut Deluxe'],
            [
                'category_id' => $category->id,
                'description' => 'Premium haircut with styling',
                'duration_minutes' => 45,
                'price' => 150000,
                'status' => 1,
                'service_type' => 'service',
                'consultation_credit_percentage' => 0,
            ]
        );
    }

    /**
     * Ensure a staff member exists.
     * اطمینان از وجود یک کارمند
     */
    private function ensureStaff(BeautySalon $salon): BeautyStaff
    {
        return BeautyStaff::firstOrCreate(
            ['salon_id' => $salon->id, 'email' => 'staff1@beauty.com'],
            [
                'name' => 'Sarah Demo',
                'phone' => '09120000011',
                'specializations' => ['haircut', 'styling'],
                'working_hours' => [
                    'monday' => ['open' => '09:00', 'close' => '18:00'],
                    'tuesday' => ['open' => '09:00', 'close' => '18:00'],
                    'wednesday' => ['open' => '09:00', 'close' => '18:00'],
                    'thursday' => ['open' => '09:00', 'close' => '18:00'],
                    'friday' => ['open' => '09:00', 'close' => '18:00'],
                ],
                'breaks' => [
                    ['start' => '13:00', 'end' => '14:00', 'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']],
                ],
                'days_off' => [],
                'status' => 1,
            ]
        );
    }

    /**
     * Ensure the demo customer exists.
     * اطمینان از وجود مشتری دمو
     */
    private function ensureCustomer(): User
    {
        return User::firstOrCreate(
            ['email' => 'john@customer.com'],
            [
                'f_name' => 'John',
                'l_name' => 'Customer',
                'phone' => '09120000001',
                'password' => bcrypt('12345678'),
                'cm_firebase_token' => 'demo_token_' . Str::random(8),
            ]
        );
    }

    /**
     * Create sample bookings across statuses.
     * ایجاد رزرو نمونه با وضعیت‌های مختلف
     */
    private function createBookings(BeautySalon $salon, BeautyService $service, BeautyStaff $staff, User $customer): void
    {
        $now = Carbon::now();

        // Upcoming confirmed
        BeautyBooking::updateOrCreate(
            ['booking_reference' => 'BB-DEMO-UPCOMING'],
            [
                'user_id' => $customer->id,
                'salon_id' => $salon->id,
                'zone_id' => $salon->zone_id,
                'service_id' => $service->id,
                'staff_id' => $staff->id,
                'booking_date' => $now->copy()->addDays(3)->format('Y-m-d'),
                'booking_time' => '11:00:00',
                'booking_date_time' => $now->copy()->addDays(3)->setTime(11, 0),
                'total_amount' => 200000,
                'service_fee' => 3000,
                'commission_amount' => 20000,
                'payment_method' => 'cash_payment',
                'payment_status' => 'unpaid',
                'status' => 'confirmed',
            ]
        );

        // Completed with paid
        BeautyBooking::updateOrCreate(
            ['booking_reference' => 'BB-DEMO-COMPLETED'],
            [
                'user_id' => $customer->id,
                'salon_id' => $salon->id,
                'zone_id' => $salon->zone_id,
                'service_id' => $service->id,
                'staff_id' => $staff->id,
                'booking_date' => $now->copy()->subDays(5)->format('Y-m-d'),
                'booking_time' => '15:00:00',
                'booking_date_time' => $now->copy()->subDays(5)->setTime(15, 0),
                'total_amount' => 250000,
                'service_fee' => 5000,
                'commission_amount' => 25000,
                'payment_method' => 'digital_payment',
                'payment_status' => 'paid',
                'status' => 'completed',
            ]
        );

        // Cancelled
        BeautyBooking::updateOrCreate(
            ['booking_reference' => 'BB-DEMO-CANCELLED'],
            [
                'user_id' => $customer->id,
                'salon_id' => $salon->id,
                'zone_id' => $salon->zone_id,
                'service_id' => $service->id,
                'staff_id' => $staff->id,
                'booking_date' => $now->copy()->subDays(2)->format('Y-m-d'),
                'booking_time' => '10:00:00',
                'booking_date_time' => $now->copy()->subDays(2)->setTime(10, 0),
                'total_amount' => 180000,
                'service_fee' => 4000,
                'commission_amount' => 18000,
                'payment_method' => 'wallet',
                'payment_status' => 'unpaid',
                'status' => 'cancelled',
                'cancellation_reason' => 'Customer request',
                'cancelled_by' => 'customer',
            ]
        );
    }

    /**
     * Create a review for the completed booking.
     * ایجاد یک نظر برای رزرو تکمیل‌شده
     */
    private function createReviews(User $customer): void
    {
        $completed = BeautyBooking::where('booking_reference', 'BB-DEMO-COMPLETED')->first();
        if (!$completed) {
            return;
        }

        BeautyReview::updateOrCreate(
            ['booking_id' => $completed->id],
            [
                'user_id' => $customer->id,
                'salon_id' => $completed->salon_id,
                'service_id' => $completed->service_id,
                'rating' => 5,
                'comment' => 'Great service and staff!',
                'attachments' => [],
                'status' => 'approved',
            ]
        );
    }

    /**
     * Create a package for the salon.
     * ایجاد یک پکیج برای سالن
     */
    private function createPackage(BeautySalon $salon, BeautyService $service): BeautyPackage
    {
        return BeautyPackage::firstOrCreate(
            ['salon_id' => $salon->id, 'name' => 'Demo Haircut Package'],
            [
                'service_id' => $service->id,
                'sessions_count' => 5,
                'total_sessions' => 5,
                'used_sessions' => 1,
                'total_price' => $service->price * 4, // discounted
                'discount_percentage' => 20,
                'validity_days' => 90,
                'status' => 1,
            ]
        );
    }

    /**
     * Create package usage for the customer.
     * ایجاد استفاده از پکیج برای مشتری
     */
    private function createPackageUsage(BeautyPackage $package, User $customer): void
    {
        BeautyPackageUsage::firstOrCreate(
            [
                'package_id' => $package->id,
                'user_id' => $customer->id,
                'session_number' => 1,
            ],
            [
                'salon_id' => $package->salon_id,
                'status' => 'pending',
                'used_at' => Carbon::now(),
                'booking_id' => null,
            ]
        );
    }

    /**
     * Create gift cards for the customer.
     * ایجاد کارت هدیه برای مشتری
     */
    private function createGiftCards(BeautySalon $salon, User $customer): void
    {
        BeautyGiftCard::firstOrCreate(
            ['code' => 'GC-DEMO-001'],
            [
                'salon_id' => $salon->id,
                'purchased_by' => $customer->id,
                'redeemed_by' => null,
                'amount' => 200000,
                'expires_at' => Carbon::now()->addMonths(6),
                'status' => 'active',
            ]
        );
    }

    /**
     * Create loyalty points for the customer.
     * ایجاد امتیاز وفاداری برای مشتری
     */
    private function createLoyaltyPoints(User $customer): void
    {
        // Earned points
        BeautyLoyaltyPoint::firstOrCreate(
            ['user_id' => $customer->id, 'type' => 'earned', 'description' => 'booking_bonus_demo'],
            [
                'points' => 500,
                'salon_id' => null,
                'campaign_id' => null,
                'booking_id' => null,
                'expires_at' => null,
            ]
        );

        // Redeemed points
        BeautyLoyaltyPoint::firstOrCreate(
            ['user_id' => $customer->id, 'type' => 'redeemed', 'description' => 'wallet_redeem_demo'],
            [
                'points' => -200,
                'salon_id' => null,
                'campaign_id' => null,
                'booking_id' => null,
                'expires_at' => null,
            ]
        );
    }

    /**
     * Create wallet transactions tagged for beauty.
     * ایجاد تراکنش‌های کیف پول با تگ زیبایی
     */
    private function createWalletTransactions(User $customer): void
    {
        WalletTransaction::firstOrCreate(
            ['transaction_id' => 'BTX-DEMO-BOOKING'],
            [
                'user_id' => $customer->id,
                'transaction_type' => 'beauty_booking',
                'credit' => 0,
                'debit' => 180000,
                'balance' => 500000,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ]
        );

        WalletTransaction::firstOrCreate(
            ['transaction_id' => 'BTX-DEMO-PACKAGE'],
            [
                'user_id' => $customer->id,
                'transaction_type' => 'beauty_package_purchase',
                'credit' => 0,
                'debit' => 400000,
                'balance' => 320000,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ]
        );
    }

    /**
     * Create a notification for the customer.
     * ایجاد یک نوتیفیکیشن برای مشتری
     */
    private function createNotifications(User $customer, BeautySalon $salon): void
    {
        UserNotification::firstOrCreate(
            ['user_id' => $customer->id, 'order_type' => 'beauty_booking', 'data' => json_encode(['title' => 'Booking Confirmed'])],
            [
                'data' => json_encode([
                    'title' => 'Booking Confirmed',
                    'description' => "Your booking at {$salon->store?->name} is confirmed.",
                    'type' => 'booking_confirmed',
                    'order_type' => 'beauty_booking',
                ]),
                'read_at' => null,
            ]
        );
    }
}

