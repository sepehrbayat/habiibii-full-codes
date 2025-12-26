<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use App\Models\Store;
use App\Models\User;
use App\Models\Zone;
use App\Models\Vendor;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyServiceCategory;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyStaff;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyReview;
use Modules\BeautyBooking\Entities\BeautyPackage;
use Modules\BeautyBooking\Entities\BeautyGiftCard;
use Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign;
use Modules\BeautyBooking\Entities\BeautyRetailProduct;
use Modules\BeautyBooking\Entities\BeautySubscription;
use Modules\BeautyBooking\Entities\BeautyMonthlyReport;
use Modules\BeautyBooking\Services\BeautyBadgeService;
use Modules\BeautyBooking\Services\BeautySalonService;

/**
 * Beauty Booking Test Data Seeder
 * Seeder داده‌های تست ماژول رزرو زیبایی
 *
 * Creates comprehensive test data for testing all features
 * ایجاد داده‌های تست جامع برای تست تمام ویژگی‌ها
 */
class BeautyBookingTestDataSeeder extends Seeder
{
    /**
     * Vendor id used for seeded salons/stores
     * شناسه وندور مورد استفاده برای سالن‌ها و فروشگاه‌های seeding شده
     *
     * @var int
     */
    private int $vendorId;

    /**
     * Run the database seeds.
     * اجرای seedها
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('🌺 Starting Beauty Booking test data seeding...');

        // Ensure a vendor exists for linking salons/stores
        // اطمینان از وجود یک وندور برای اتصال سالن‌ها/فروشگاه‌ها
        $this->vendorId = $this->ensureVendor();
        
        // Get or create zones
        // دریافت یا ایجاد مناطق
        $zones = $this->getOrCreateZones();
        
        // Create stores and salons
        // ایجاد فروشگاه‌ها و سالن‌ها
        $salons = $this->createSalons($zones);
        
        // Create service categories (if not exists)
        // ایجاد دسته‌بندی‌های خدمت (در صورت عدم وجود)
        $categories = $this->ensureCategories();
        
        // Create services for each salon
        // ایجاد خدمات برای هر سالن
        $this->createServices($salons, $categories);
        
        // Create staff for each salon
        // ایجاد کارمندان برای هر سالن
        $staffMembers = $this->createStaff($salons);
        
        // Create test users (customers)
        // ایجاد کاربران تست (مشتریان)
        $users = $this->createTestUsers();
        
        // Create bookings
        // ایجاد رزروها
        $this->createBookings($salons, $users, $staffMembers);
        
        // Create reviews
        // ایجاد نظرات
        $this->createReviews($salons, $users);
        
        // Create packages
        // ایجاد پکیج‌ها
        $this->createPackages($salons);
        
        // Create gift cards
        // ایجاد کارت‌های هدیه
        $this->createGiftCards($salons);
        
        // Create loyalty campaigns
        // ایجاد کمپین‌های وفاداری
        $this->createLoyaltyCampaigns($salons);
        
        // Create retail products
        // ایجاد محصولات خرده‌فروشی
        $this->createRetailProducts($salons);
        
        // Create subscriptions
        // ایجاد اشتراک‌ها
        $this->createSubscriptions($salons);
        
        // Create monthly reports
        // ایجاد گزارش‌های ماهانه
        $this->createMonthlyReports($salons);
        
        // Recalculate badges and statistics
        // محاسبه مجدد نشان‌ها و آمار
        $this->recalculateStatistics($salons);
        
        $this->command->info('✅ Beauty Booking test data seeding completed!');
    }
    
    /**
     * Get or create zones
     * دریافت یا ایجاد مناطق
     *
     * @return \Illuminate\Support\Collection
     */
    private function getOrCreateZones()
    {
        $zones = Zone::take(3)->get();

        // If no zones exist, create a minimal default zone (without spatial data)
        // اگر هیچ منطقه‌ای وجود ندارد، یک منطقه پیش‌فرض حداقلی (بدون داده مکانی) بساز
        if ($zones->isEmpty()) {
            $zones->push(Zone::create([
                'name' => 'Default Zone',
                'display_name' => 'Default Zone',
                'coordinates' => null,
                'status' => 1,
                'cash_on_delivery' => 1,
                'digital_payment' => 1,
                'offline_payment' => 1,
            ]));
        }

        // Pad collection to 3 entries by reusing the first zone id (safe for seed data)
        // برای رسیدن به ۳ ورودی، از شناسه اولین منطقه استفاده مجدد کن (برای داده تست)
        while ($zones->count() < 3) {
            $zones->push($zones->first());
        }

        return $zones->take(3);
    }
    
    /**
     * Create salons with different statuses
     * ایجاد سالن‌ها با وضعیت‌های مختلف
     *
     * @param \Illuminate\Support\Collection $zones
     * @return \Illuminate\Support\Collection
     */
    private function createSalons($zones)
    {
        $salons = collect();
        
        // Default working hours
        // ساعات کاری پیش‌فرض
        $defaultWorkingHours = [
            'monday' => ['open' => '09:00', 'close' => '18:00'],
            'tuesday' => ['open' => '09:00', 'close' => '18:00'],
            'wednesday' => ['open' => '09:00', 'close' => '18:00'],
            'thursday' => ['open' => '09:00', 'close' => '18:00'],
            'friday' => ['open' => '09:00', 'close' => '18:00'],
            'saturday' => ['open' => '10:00', 'close' => '16:00'],
            'sunday' => null, // Closed
        ];
        
        // Salon 1: Approved and Verified (Top Rated candidate)
        // سالن 1: تأیید شده و معتبر (کاندید Top Rated)
        $moduleId = DB::table('modules')
            ->where('module_name', 'beauty')
            ->orWhere('module_name', 'Beauty Booking')
            ->orWhere('module_name', 'beauty_booking')
            ->orWhere('module_name', 'BeautyBooking')
            ->value('id') ?? DB::table('modules')->where('module_name', 'LIKE', '%beauty%')->value('id') ?? null;
        
        $store1 = Store::firstOrCreate(
            ['phone' => '02112345678'],
            [
                'name' => 'Elite Beauty Salon',
                'email' => 'elite@beauty.com',
            'logo' => 'def.png',
            'cover_photo' => 'def.png',
            'latitude' => 35.6892,
            'longitude' => 51.3890,
            'zone_id' => $zones->first()->id ?? null,
            'module_id' => $moduleId,
            'status' => 1,
            'active' => 1,
                'vendor_id' => $this->vendorId,
            ]
        );
        
        $salon1 = BeautySalon::firstOrCreate(
            ['store_id' => $store1->id],
            [
            'store_id' => $store1->id,
            'zone_id' => $zones->first()->id ?? null,
            'business_type' => 'salon',
            'license_number' => 'LIC-001',
            'license_expiry' => Carbon::now()->addYear(),
            'documents' => ['doc1.pdf', 'doc2.jpg'],
            'verification_status' => 1, // Approved
            'is_verified' => true,
            'is_featured' => true,
            'working_hours' => $defaultWorkingHours,
            'holidays' => [],
            'avg_rating' => 4.8,
            'total_bookings' => 0, // Will be updated
            'total_reviews' => 0, // Will be updated
            'total_cancellations' => 0,
                'cancellation_rate' => 0.0,
            ]
        );
        $salons->push($salon1);
        
        // Salon 2: Approved Clinic (Trending candidate)
        // سالن 2: کلینیک تأیید شده (کاندید Trending)
        $store2 = Store::firstOrCreate(
            ['phone' => '02112345679'],
            [
                'name' => 'Premium Skin Clinic',
                'email' => 'premium@clinic.com',
            'logo' => 'def.png',
            'cover_photo' => 'def.png',
            'latitude' => 35.6992,
            'longitude' => 51.3990,
            'zone_id' => $zones->skip(1)->first()->id ?? null,
            'module_id' => $moduleId,
            'status' => 1,
            'active' => 1,
                'vendor_id' => $this->vendorId,
            ]
        );
        
        $salon2 = BeautySalon::firstOrCreate(
            ['store_id' => $store2->id],
            [
            'store_id' => $store2->id,
            'zone_id' => $zones->skip(1)->first()->id ?? null,
            'business_type' => 'clinic',
            'license_number' => 'LIC-002',
            'license_expiry' => Carbon::now()->addYear(),
            'documents' => ['doc1.pdf'],
            'verification_status' => 1,
            'is_verified' => true,
            'is_featured' => false,
            'working_hours' => $defaultWorkingHours,
            'holidays' => [],
            'avg_rating' => 4.6,
            'total_bookings' => 0,
            'total_reviews' => 0,
            'total_cancellations' => 0,
                'cancellation_rate' => 0.0,
            ]
        );
        $salons->push($salon2);
        
        // Salon 3: Pending Approval
        // سالن 3: در انتظار تأیید
        $store3 = Store::firstOrCreate(
            ['phone' => '02112345680'],
            [
                'name' => 'New Beauty Center',
                'email' => 'new@beauty.com',
            'logo' => 'def.png',
            'cover_photo' => 'def.png',
            'latitude' => 35.7092,
            'longitude' => 51.4090,
            'zone_id' => $zones->last()->id ?? null,
            'module_id' => $moduleId,
            'status' => 1,
            'active' => 1,
                'vendor_id' => $this->vendorId,
            ]
        );
        
        $salon3 = BeautySalon::firstOrCreate(
            ['store_id' => $store3->id],
            [
            'store_id' => $store3->id,
            'zone_id' => $zones->last()->id ?? null,
            'business_type' => 'salon',
            'license_number' => 'LIC-003',
            'license_expiry' => Carbon::now()->addMonths(6),
            'documents' => ['doc1.pdf'],
            'verification_status' => 0, // Pending
            'is_verified' => false,
            'is_featured' => false,
            'working_hours' => $defaultWorkingHours,
            'holidays' => [],
            'avg_rating' => 0.0,
            'total_bookings' => 0,
            'total_reviews' => 0,
            'total_cancellations' => 0,
                'cancellation_rate' => 0.0,
            ]
        );
        $salons->push($salon3);
        
        // Salon 4: Rejected
        // سالن 4: رد شده
        $store4 = Store::firstOrCreate(
            ['phone' => '02112345681'],
            [
                'name' => 'Rejected Salon',
                'email' => 'rejected@beauty.com',
            'logo' => 'def.png',
            'cover_photo' => 'def.png',
            'latitude' => 35.7192,
            'longitude' => 51.4190,
            'zone_id' => $zones->first()->id ?? null,
            'module_id' => $moduleId,
            'status' => 0,
            'active' => 0,
                'vendor_id' => $this->vendorId,
            ]
        );
        
        $salon4 = BeautySalon::firstOrCreate(
            ['store_id' => $store4->id],
            [
            'store_id' => $store4->id,
            'zone_id' => $zones->first()->id ?? null,
            'business_type' => 'salon',
            'license_number' => 'LIC-004',
            'license_expiry' => Carbon::now()->subMonths(1), // Expired
            'documents' => [],
            'verification_status' => 2, // Rejected
            'verification_notes' => 'License expired',
            'is_verified' => false,
            'is_featured' => false,
            'working_hours' => $defaultWorkingHours,
            'holidays' => [],
            'avg_rating' => 0.0,
            'total_bookings' => 0,
            'total_reviews' => 0,
            'total_cancellations' => 0,
                'cancellation_rate' => 0.0,
            ]
        );
        $salons->push($salon4);
        
        $this->command->info("✅ Created {$salons->count()} salons");
        
        return $salons;
    }

    /**
     * Ensure at least one vendor exists and return its id
     * اطمینان از وجود حداقل یک وندور و برگرداندن شناسه آن
     *
     * @return int
     */
    private function ensureVendor(): int
    {
        $vendor = Vendor::first();

        if (!$vendor) {
            $vendor = Vendor::create([
                'f_name' => 'Beauty',
                'l_name' => 'Vendor',
                'email' => 'beauty-vendor@example.com',
                'phone' => '09120000000',
                'password' => bcrypt('12345678'),
                'status' => 1,
            ]);
        }

        return $vendor->id;
    }
    
    /**
     * Ensure categories exist
     * اطمینان از وجود دسته‌بندی‌ها
     *
     * @return \Illuminate\Support\Collection
     */
    private function ensureCategories()
    {
        $categories = BeautyServiceCategory::all();
        
        if ($categories->isEmpty()) {
            // Run the main seeder first
            // اجرای seeder اصلی ابتدا
            $this->call(BeautyBookingDatabaseSeeder::class);
            $categories = BeautyServiceCategory::all();
        }
        
        return $categories;
    }
    
    /**
     * Create services for salons
     * ایجاد خدمات برای سالن‌ها
     *
     * @param \Illuminate\Support\Collection $salons
     * @param \Illuminate\Support\Collection $categories
     * @return void
     */
    private function createServices($salons, $categories): void
    {
        $services = [];
        
        $serviceData = [
            ['name' => 'Haircut', 'price' => 100000, 'duration' => 30],
            ['name' => 'Hair Color', 'price' => 300000, 'duration' => 120],
            ['name' => 'Facial Treatment', 'price' => 200000, 'duration' => 60],
            ['name' => 'Manicure', 'price' => 80000, 'duration' => 45],
            ['name' => 'Pedicure', 'price' => 100000, 'duration' => 60],
            ['name' => 'Makeup', 'price' => 250000, 'duration' => 90],
            ['name' => 'Massage', 'price' => 150000, 'duration' => 60],
            ['name' => 'Skin Consultation', 'price' => 50000, 'duration' => 30],
        ];
        
        foreach ($salons->where('verification_status', 1) as $salon) {
            $category = $categories->random();
            
            foreach ($serviceData as $index => $data) {
                $services[] = BeautyService::create([
                    'salon_id' => $salon->id,
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'description' => "Professional {$data['name']} service",
                    'duration_minutes' => $data['duration'],
                    'price' => $data['price'],
                    'status' => 1,
                    'service_type' => 'service',
                    'consultation_credit_percentage' => 0,
                ]);
            }
        }
        
        $this->command->info("✅ Created " . count($services) . " services");
    }
    
    /**
     * Create staff members for salons
     * ایجاد کارمندان برای سالن‌ها
     *
     * @param \Illuminate\Support\Collection $salons
     * @return \Illuminate\Support\Collection
     */
    private function createStaff($salons)
    {
        $allStaff = collect();
        
        $staffNames = [
            'Sarah Johnson',
            'Emma Williams',
            'Olivia Brown',
            'Sophia Davis',
            'Isabella Miller',
        ];
        
        foreach ($salons->where('verification_status', 1) as $salon) {
            $salonStaff = collect();
            
            foreach ($staffNames as $index => $name) {
                $workingHours = [
                    'monday' => ['open' => '09:00', 'close' => '18:00'],
                    'tuesday' => ['open' => '09:00', 'close' => '18:00'],
                    'wednesday' => ['open' => '09:00', 'close' => '18:00'],
                    'thursday' => ['open' => '09:00', 'close' => '18:00'],
                    'friday' => ['open' => '09:00', 'close' => '18:00'],
                    'saturday' => ['open' => '10:00', 'close' => '16:00'],
                    'sunday' => null,
                ];
                
                $staff = BeautyStaff::create([
                    'salon_id' => $salon->id,
                    'name' => $name,
                    'phone' => '0912' . rand(1000000, 9999999),
                    'email' => strtolower(explode(' ', $name)[0]) . '@salon.com',
                    'specializations' => ['haircut', 'styling'],
                    'working_hours' => $workingHours,
                    'breaks' => [
                        ['start' => '13:00', 'end' => '14:00', 'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']]
                    ],
                    'days_off' => [],
                    'status' => 1,
                ]);
                
                $salonStaff->push($staff);
                $allStaff->push($staff);
            }
            
            // Link all staff to all services for this salon
            // اتصال همه کارمندان به همه خدمات این سالن
            $services = BeautyService::where('salon_id', $salon->id)->get();
            foreach ($services as $service) {
                // Attach all staff members to each service
                // اتصال همه کارمندان به هر خدمت
                $service->staff()->syncWithoutDetaching($salonStaff->pluck('id')->toArray());
            }
        }
        
        $this->command->info("✅ Created {$allStaff->count()} staff members");
        
        return $allStaff;
    }
    
    /**
     * Create test users (customers)
     * ایجاد کاربران تست (مشتریان)
     *
     * @return \Illuminate\Support\Collection
     */
    private function createTestUsers()
    {
        $users = collect();
        
        $userData = [
            ['f_name' => 'John', 'l_name' => 'Doe', 'email' => 'john@customer.com'],
            ['f_name' => 'Jane', 'l_name' => 'Smith', 'email' => 'jane@customer.com'],
            ['f_name' => 'Mike', 'l_name' => 'Johnson', 'email' => 'mike@customer.com'],
            ['f_name' => 'Lisa', 'l_name' => 'Brown', 'email' => 'lisa@customer.com'],
            ['f_name' => 'David', 'l_name' => 'Wilson', 'email' => 'david@customer.com'],
        ];
        
        foreach ($userData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'f_name' => $data['f_name'],
                    'l_name' => $data['l_name'],
                    'phone' => '0912' . rand(1000000, 9999999),
                    'password' => bcrypt('12345678'),
                    'cm_firebase_token' => 'test_token_' . Str::random(10),
                ]
            );
            $users->push($user);
        }
        
        $this->command->info("✅ Created/Found {$users->count()} test users");
        
        return $users;
    }
    
    /**
     * Create bookings with different statuses
     * ایجاد رزروها با وضعیت‌های مختلف
     *
     * @param \Illuminate\Support\Collection $salons
     * @param \Illuminate\Support\Collection $users
     * @param \Illuminate\Support\Collection $staffMembers
     * @return void
     */
    private function createBookings($salons, $users, $staffMembers): void
    {
        $bookings = [];
        $approvedSalons = $salons->where('verification_status', 1);
        
        // Get initial max ID before any bookings are created to avoid duplicate references
        // دریافت حداکثر ID اولیه قبل از ایجاد هر رزروی برای جلوگیری از مراجع تکراری
        $baseBookingId = BeautyBooking::max('id') ?? 99999;
        $bookingCounter = 0;
        
        foreach ($approvedSalons as $salon) {
            $services = BeautyService::where('salon_id', $salon->id)->get();
            $salonStaff = $staffMembers->where('salon_id', $salon->id);
            
            if ($services->isEmpty()) {
                continue;
            }
            
            // Create bookings for past 30 days
            // ایجاد رزروها برای 30 روز گذشته
            
            for ($i = 0; $i < 60; $i++) {
                $bookingDate = Carbon::now()->subDays(rand(0, 30));
                $bookingTime = Carbon::createFromTime(rand(9, 17), rand(0, 59), 0);
                
                $service = $services->random();
                $user = $users->random();
                $staff = $salonStaff->isNotEmpty() ? $salonStaff->random() : null;
                
                // Different statuses
                // وضعیت‌های مختلف
                $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
                $status = $statuses[array_rand($statuses)];
                
                // Calculate amounts
                // محاسبه مبالغ
                $basePrice = $service->price;
                $serviceFee = $basePrice * 0.02; // 2%
                $commissionAmount = $basePrice * 0.10; // 10%
                // Customer pays base + service fee; commission is platform share from salon side
                // مشتری پایه + کارمزد سرویس را می‌پردازد؛ کمیسیون از سمت سالن کسر می‌شود
                $totalAmount = $basePrice + $serviceFee + $commissionAmount;
                
                $paymentMethods = ['cash_payment', 'digital_payment', 'wallet'];
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
                
                // Generate unique booking reference using counter to avoid duplicates
                // تولید شماره رزرو یکتا با استفاده از شمارنده برای جلوگیری از تکرار
                $bookingCounter++;
                $bookingReference = 'BB-' . str_pad((string)($baseBookingId + $bookingCounter), 6, '0', STR_PAD_LEFT);
                
                $booking = BeautyBooking::create([
                    'user_id' => $user->id,
                    'salon_id' => $salon->id,
                    'zone_id' => $salon->zone_id,
                    'service_id' => $service->id,
                    'staff_id' => $staff ? $staff->id : null,
                    'booking_date' => $bookingDate->format('Y-m-d'),
                    'booking_time' => $bookingTime->format('H:i:s'),
                    'booking_date_time' => $bookingDate->copy()->setTimeFromTimeString($bookingTime->format('H:i:s')),
                    'booking_reference' => $bookingReference,
                    'total_amount' => $totalAmount,
                    'service_fee' => $serviceFee,
                    'commission_amount' => $commissionAmount,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $status === 'completed' ? 'paid' : ($status === 'cancelled' ? 'unpaid' : 'unpaid'),
                    'status' => $status,
                    'cancellation_reason' => $status === 'cancelled' ? 'Customer request' : null,
                    'cancelled_by' => $status === 'cancelled' ? 'customer' : 'none',
                ]);
                
                $bookings[] = $booking;
            }
        }
        
        $this->command->info("✅ Created " . count($bookings) . " bookings");
    }
    
    /**
     * Create reviews for bookings
     * ایجاد نظرات برای رزروها
     *
     * @param \Illuminate\Support\Collection $salons
     * @param \Illuminate\Support\Collection $users
     * @return void
     */
    private function createReviews($salons, $users): void
    {
        $reviews = [];
        $completedBookings = BeautyBooking::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(60))
            ->get();
        
        foreach ($completedBookings->take(30) as $booking) {
            $rating = rand(4, 5); // High ratings for test data
            
            $review = BeautyReview::create([
                'user_id' => $booking->user_id,
                'salon_id' => $booking->salon_id,
                'service_id' => $booking->service_id,
                'booking_id' => $booking->id,
                'rating' => $rating,
                'comment' => "Great service! Very professional and friendly staff.",
                'attachments' => [],
                'status' => 'approved', // Auto-approved for test
            ]);
            
            $reviews[] = $review;
        }
        
        $this->command->info("✅ Created " . count($reviews) . " reviews");
    }
    
    /**
     * Create packages
     * ایجاد پکیج‌ها
     *
     * @param \Illuminate\Support\Collection $salons
     * @return void
     */
    private function createPackages($salons): void
    {
        $packages = [];
        
        foreach ($salons->where('verification_status', 1) as $salon) {
            $services = BeautyService::where('salon_id', $salon->id)->take(3)->get();
            
            if ($services->isEmpty()) {
                continue;
            }
            
            $service = $services->first();
            $basePrice = $service->price * 10; // 10 sessions
            $discountedPrice = $basePrice * 0.8; // 20% discount
            
            $package = BeautyPackage::create([
                'salon_id' => $salon->id,
                'service_id' => $service->id,
                'name' => 'Premium Package - ' . $service->name,
                'sessions_count' => 10,
                'total_sessions' => 10,
                'used_sessions' => 0,
                'total_price' => $discountedPrice,
                'discount_percentage' => 20,
                'validity_days' => 180,
                'status' => 1,
            ]);
            
            $packages[] = $package;
        }
        
        $this->command->info("✅ Created " . count($packages) . " packages");
    }
    
    /**
     * Create gift cards
     * ایجاد کارت‌های هدیه
     *
     * @param \Illuminate\Support\Collection $salons
     * @return void
     */
    private function createGiftCards($salons): void
    {
        $giftCards = [];
        
        foreach ($salons->where('verification_status', 1)->take(2) as $salon) {
            for ($i = 0; $i < 5; $i++) {
                $user = User::first();
                $giftCard = BeautyGiftCard::create([
                    'salon_id' => $salon->id,
                    'code' => 'GC-' . strtoupper(Str::random(8)),
                    'purchased_by' => $user->id,
                    'redeemed_by' => null,
                    'amount' => Arr::random([100000, 200000, 500000]),
                    'expires_at' => Carbon::now()->addMonths(6),
                    'status' => 'active',
                ]);
                
                $giftCards[] = $giftCard;
            }
        }
        
        $this->command->info("✅ Created " . count($giftCards) . " gift cards");
    }
    
    /**
     * Create loyalty campaigns
     * ایجاد کمپین‌های وفاداری
     *
     * @param \Illuminate\Support\Collection $salons
     * @return void
     */
    private function createLoyaltyCampaigns($salons): void
    {
        $campaigns = [];
        
        foreach ($salons->where('verification_status', 1)->take(2) as $salon) {
            $campaign = BeautyLoyaltyCampaign::create([
                'salon_id' => $salon->id,
                'name' => 'Loyalty Rewards Program',
                'description' => 'Earn points with every booking',
                'type' => 'points',
                'rules' => [
                    'points_per_booking' => 10,
                    'points_per_amount' => 1, // 1 point per 1000 Toman
                    'redemption_rate' => 100, // 100 points = 10000 Toman
                ],
                'start_date' => Carbon::now()->subMonths(1)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(6)->format('Y-m-d'),
                'is_active' => true,
                'commission_percentage' => 0,
                'commission_type' => 'percentage',
                'total_participants' => 0,
                'total_redeemed' => 0,
                'total_revenue' => 0,
            ]);
            
            $campaigns[] = $campaign;
        }
        
        $this->command->info("✅ Created " . count($campaigns) . " loyalty campaigns");
    }
    
    /**
     * Create retail products
     * ایجاد محصولات خرده‌فروشی
     *
     * @param \Illuminate\Support\Collection $salons
     * @return void
     */
    private function createRetailProducts($salons): void
    {
        $products = [];
        
        $productData = [
            ['name' => 'Hair Shampoo', 'price' => 50000, 'stock' => 100],
            ['name' => 'Face Cream', 'price' => 80000, 'stock' => 50],
            ['name' => 'Hair Serum', 'price' => 120000, 'stock' => 30],
            ['name' => 'Nail Polish Set', 'price' => 60000, 'stock' => 40],
        ];
        
        foreach ($salons->where('verification_status', 1)->take(2) as $salon) {
            foreach ($productData as $data) {
                $product = BeautyRetailProduct::create([
                    'salon_id' => $salon->id,
                    'name' => $data['name'],
                    'description' => "Premium quality {$data['name']}",
                    'price' => $data['price'],
                    'stock_quantity' => $data['stock'],
                    'category' => 'skincare',
                    'status' => 1,
                ]);
                
                $products[] = $product;
            }
        }
        
        $this->command->info("✅ Created " . count($products) . " retail products");
    }
    
    /**
     * Create subscriptions
     * ایجاد اشتراک‌ها
     *
     * @param \Illuminate\Support\Collection $salons
     * @return void
     */
    private function createSubscriptions($salons): void
    {
        $subscriptions = [];
        
        foreach ($salons->where('verification_status', 1)->take(2) as $salon) {
            // Featured listing subscription
            // اشتراک Featured Listing
            $subscription1 = BeautySubscription::create([
                'salon_id' => $salon->id,
                'subscription_type' => 'featured_listing',
                'duration_days' => 30,
                'start_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
                'amount_paid' => 150000,
                'status' => 'active',
            ]);
            $subscriptions[] = $subscription1;
            
            // Boost ads subscription
            // اشتراک Boost Ads
            $subscription2 = BeautySubscription::create([
                'salon_id' => $salon->id,
                'subscription_type' => 'boost_ads',
                'duration_days' => 7,
                'start_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'amount_paid' => 75000,
                'status' => 'active',
            ]);
            $subscriptions[] = $subscription2;
        }
        
        $this->command->info("✅ Created " . count($subscriptions) . " subscriptions");
    }
    
    /**
     * Create monthly reports
     * ایجاد گزارش‌های ماهانه
     *
     * @param \Illuminate\Support\Collection $salons
     * @return void
     */
    private function createMonthlyReports($salons): void
    {
        $reports = [];
        $lastMonth = Carbon::now()->subMonth();
        
        // Top Rated Salons report
        // گزارش سالن‌های Top Rated
        $topRatedSalons = $salons->where('verification_status', 1)->take(5);
        $topRatedSalonIds = $topRatedSalons->pluck('id')->toArray();
        $topRatedSalonData = [];
        foreach ($topRatedSalons as $index => $salon) {
            $topRatedSalonData[] = [
                'salon_id' => $salon->id,
                'rank' => $index + 1,
                'avg_rating' => 4.5 + ($index * 0.1),
                'total_bookings' => 50 + ($index * 10),
            ];
        }
        
        $report1 = BeautyMonthlyReport::create([
            'report_type' => 'top_rated_salons',
            'month' => $lastMonth->month,
            'year' => $lastMonth->year,
            'salon_ids' => $topRatedSalonIds,
            'salon_data' => $topRatedSalonData,
            'total_salons' => count($topRatedSalonIds),
        ]);
        $reports[] = $report1;
        
        // Trending Clinics report
        // گزارش کلینیک‌های Trending
        $trendingClinics = $salons->where('business_type', 'clinic')->where('verification_status', 1)->take(5);
        $trendingClinicIds = $trendingClinics->pluck('id')->toArray();
        $trendingClinicData = [];
        foreach ($trendingClinics as $index => $salon) {
            $trendingClinicData[] = [
                'salon_id' => $salon->id,
                'rank' => $index + 1,
                'bookings_count' => 30 + ($index * 5),
                'growth_rate' => 15 + ($index * 5),
            ];
        }
        
        $report2 = BeautyMonthlyReport::create([
            'report_type' => 'trending_clinics',
            'month' => $lastMonth->month,
            'year' => $lastMonth->year,
            'salon_ids' => $trendingClinicIds,
            'salon_data' => $trendingClinicData,
            'total_salons' => count($trendingClinicIds),
        ]);
        $reports[] = $report2;
        
        $this->command->info("✅ Created " . count($reports) . " monthly reports");
    }
    
    /**
     * Recalculate statistics and badges
     * محاسبه مجدد آمار و نشان‌ها
     *
     * @param \Illuminate\Support\Collection $salons
     * @return void
     */
    private function recalculateStatistics($salons): void
    {
        $badgeService = app(BeautyBadgeService::class);
        $salonService = app(BeautySalonService::class);
        
        foreach ($salons->where('verification_status', 1) as $salon) {
            // Update statistics using service
            // به‌روزرسانی آمار با استفاده از سرویس
            $salonService->updateSalonStatistics($salon->id);
            
            // Update rating statistics
            // به‌روزرسانی آمار رتبه‌بندی
            $salonService->updateRatingStatistics($salon->id);
            
            // Recalculate badges
            // محاسبه مجدد نشان‌ها
            $badgeService->calculateAndAssignBadges($salon->id);
        }
        
        $this->command->info("✅ Recalculated statistics and badges");
    }
}

