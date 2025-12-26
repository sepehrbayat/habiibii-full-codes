<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Store;
use App\Models\Vendor;
use App\Models\Zone;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyServiceCategory;
use Modules\BeautyBooking\Entities\BeautyStaff;

/**
 * Seed multiple salons/clinics for customer choice testing
 * ایجاد سالن‌ها و کلینیک‌های متعدد برای تست انتخاب مشتری
 */
class BeautyCustomerChoicesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌺 Seeding multiple salons/clinics for customer testing...');

        $zone = $this->ensureZone();
        $vendor = $this->ensureVendor();
        $moduleId = $this->getBeautyModuleId();
        $categories = $this->ensureCategories();

        $workingHours = [
            'monday' => ['open' => '09:00', 'close' => '18:00'],
            'tuesday' => ['open' => '09:00', 'close' => '18:00'],
            'wednesday' => ['open' => '09:00', 'close' => '18:00'],
            'thursday' => ['open' => '09:00', 'close' => '18:00'],
            'friday' => ['open' => '09:00', 'close' => '18:00'],
            'saturday' => ['open' => '10:00', 'close' => '16:00'],
            'sunday' => null,
        ];

        $entries = [
            ['name' => 'City Glow Salon', 'type' => 'salon', 'phone' => '02140000001', 'lat' => 35.7001, 'lng' => 51.4011, 'featured' => true],
            ['name' => 'Uptown Hair Studio', 'type' => 'salon', 'phone' => '02140000002', 'lat' => 35.7052, 'lng' => 51.3925, 'featured' => false],
            ['name' => 'Downtown Beauty Lounge', 'type' => 'salon', 'phone' => '02140000003', 'lat' => 35.6920, 'lng' => 51.3850, 'featured' => false],
            ['name' => 'Premium Skin Clinic', 'type' => 'clinic', 'phone' => '02140000004', 'lat' => 35.6995, 'lng' => 51.4102, 'featured' => true],
            ['name' => 'Dermacare Center', 'type' => 'clinic', 'phone' => '02140000005', 'lat' => 35.6895, 'lng' => 51.3955, 'featured' => false],
        ];

        foreach ($entries as $entry) {
            $store = Store::firstOrCreate(
                ['phone' => $entry['phone']],
                [
                    'name' => $entry['name'],
                    'email' => Str::slug($entry['name'], '.') . '@demo.com',
                    'logo' => 'def.png',
                    'cover_photo' => 'def.png',
                    'latitude' => $entry['lat'],
                    'longitude' => $entry['lng'],
                    'zone_id' => $zone->id,
                    'module_id' => $moduleId,
                    'status' => 1,
                    'active' => 1,
                    'vendor_id' => $vendor->id,
                ]
            );

            $salon = BeautySalon::firstOrCreate(
                ['store_id' => $store->id],
                [
                    'store_id' => $store->id,
                    'zone_id' => $zone->id,
                    'business_type' => $entry['type'],
                    'license_number' => 'LIC-' . strtoupper(Str::random(6)),
                    'license_expiry' => Carbon::now()->addYear(),
                    'documents' => ['doc1.pdf'],
                    'verification_status' => 1,
                    'is_verified' => true,
                    'is_featured' => $entry['featured'],
                    'working_hours' => $workingHours,
                    'holidays' => [],
                    'avg_rating' => 4.5,
                    'total_bookings' => 0,
                    'total_reviews' => 0,
                    'total_cancellations' => 0,
                    'cancellation_rate' => 0.0,
                ]
            );

            $this->createServices($salon, $categories);
            $this->createStaff($salon);
        }

        $this->command->info('✅ Created multiple salons/clinics for beauty customer testing');
    }

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

    private function getBeautyModuleId(): int
    {
        return (int) DB::table('modules')
            ->whereIn('module_name', ['beauty', 'Beauty Booking'])
            ->value('id') ?: 2;
    }

    private function ensureCategories()
    {
        $categories = BeautyServiceCategory::all();
        if ($categories->isEmpty()) {
            $categories->push(BeautyServiceCategory::create(['name' => 'Hair', 'status' => 1]));
            $categories->push(BeautyServiceCategory::create(['name' => 'Skin', 'status' => 1]));
            $categories->push(BeautyServiceCategory::create(['name' => 'Nails', 'status' => 1]));
        }
        return $categories;
    }

    private function createServices(BeautySalon $salon, $categories): void
    {
        $serviceData = [
            ['name' => 'Haircut Deluxe', 'price' => 150000, 'duration' => 45],
            ['name' => 'Color & Highlights', 'price' => 350000, 'duration' => 120],
            ['name' => 'Deep Facial', 'price' => 220000, 'duration' => 60],
            ['name' => 'Manicure Classic', 'price' => 90000, 'duration' => 45],
        ];

        foreach ($serviceData as $data) {
            $category = $categories->random();
            BeautyService::firstOrCreate(
                ['salon_id' => $salon->id, 'name' => $data['name']],
                [
                    'category_id' => $category->id,
                    'description' => $data['name'] . ' service',
                    'duration_minutes' => $data['duration'],
                    'price' => $data['price'],
                    'status' => 1,
                    'service_type' => 'service',
                    'consultation_credit_percentage' => 0,
                ]
            );
        }
    }

    private function createStaff(BeautySalon $salon): void
    {
        $staffNames = ['Sarah Demo', 'Emma Demo', 'Olivia Demo'];
        foreach ($staffNames as $name) {
            $staff = BeautyStaff::firstOrCreate(
                ['salon_id' => $salon->id, 'email' => Str::slug($name, '.') . '@' . $salon->id . '.com'],
                [
                    'name' => $name,
                    'phone' => '0912' . rand(1000000, 9999999),
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

            // Attach staff to all services in this salon
            $services = BeautyService::where('salon_id', $salon->id)->pluck('id');
            $staff->services()->syncWithoutDetaching($services);
        }
    }
}

