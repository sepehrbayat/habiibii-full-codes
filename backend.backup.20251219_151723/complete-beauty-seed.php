<?php

/**
 * Complete Beauty Booking Test Data Seeder
 * Seeder کامل داده‌های تست ماژول رزرو زیبایی
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Module;
use App\Models\Zone;
use App\Models\Store;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

echo "🌺 Starting Complete Beauty Booking Test Data Seeding...\n";
echo "شروع ایجاد داده‌های تست کامل ماژول رزرو زیبایی...\n\n";

try {
    DB::beginTransaction();

    // 1. Get Module
    echo "1. Getting Beauty Booking Module...\n";
    $module = Module::where('module_name', 'beauty_booking')->first();
    if (!$module) {
        throw new \Exception("Beauty Booking module not found. Please create it first.");
    }
    echo "   Module ID: {$module->id}\n";
    echo "   Module Name: {$module->module_name}\n\n";

    // 2. Get Zone
    echo "2. Getting Zone...\n";
    $zone = Zone::where('name', 'Tehran Zone')->first();
    if (!$zone) {
        $zone = Zone::create([
            'name' => 'Tehran Zone',
            'display_name' => 'Tehran Zone',
            'status' => 1,
            'coordinates' => null,
            'cash_on_delivery' => 1,
            'digital_payment' => 1,
            'offline_payment' => 1,
        ]);
        echo "   Zone created\n";
    }
    echo "   Zone ID: {$zone->id}\n\n";

    // 3. Attach Zone to Module
    if (!$module->zones()->where('zone_id', $zone->id)->exists()) {
        $module->zones()->attach($zone->id);
        echo "3. Zone attached to module\n\n";
    }

    // 4. Get Test User
    echo "4. Getting Test User...\n";
    $user = User::where('email', 'test@6ammart.com')->first();
    if (!$user) {
        $user = User::create([
            'f_name' => 'Test',
            'l_name' => 'User',
            'email' => 'test@6ammart.com',
            'phone' => '09123456789',
            'password' => Hash::make('123456'),
            'status' => 1,
            'email_verified_at' => now(),
            'is_phone_verified' => 1,
        ]);
        echo "   User created\n";
    }
    echo "   User ID: {$user->id}\n";
    echo "   Email: {$user->email}\n\n";

    // 5. Get or Create Vendor
    echo "5. Getting Vendor...\n";
    $vendor = Vendor::where('email', 'beauty@salon.com')->first();
    if (!$vendor) {
        $vendor = Vendor::create([
            'f_name' => 'Beauty',
            'l_name' => 'Salon Owner',
            'email' => 'beauty@salon.com',
            'phone' => '09123456780',
            'password' => Hash::make('123456'),
            'status' => 1,
        ]);
        echo "   Vendor created\n";
    }
    echo "   Vendor ID: {$vendor->id}\n\n";

    // 6. Create Stores and Salons
    echo "6. Creating Stores and Salons...\n";
    $salons = [];
    
    $storeData = [
        [
            'phone' => '02112345678',
            'name' => 'Elite Beauty Salon',
            'email' => 'elite@beauty.com',
            'salon' => [
                'business_type' => 'salon',
                'license_number' => 'LIC-001',
                'verification_status' => 1,
                'is_verified' => 1,
                'is_featured' => 1,
                'avg_rating' => 4.8,
            ]
        ],
        [
            'phone' => '02112345679',
            'name' => 'Premium Skin Clinic',
            'email' => 'premium@clinic.com',
            'salon' => [
                'business_type' => 'clinic',
                'license_number' => 'LIC-002',
                'verification_status' => 1,
                'is_verified' => 1,
                'is_featured' => 0,
                'avg_rating' => 4.6,
            ]
        ],
    ];

    $workingHours = [
        'monday' => ['open' => '09:00', 'close' => '18:00'],
        'tuesday' => ['open' => '09:00', 'close' => '18:00'],
        'wednesday' => ['open' => '09:00', 'close' => '18:00'],
        'thursday' => ['open' => '09:00', 'close' => '18:00'],
        'friday' => ['open' => '09:00', 'close' => '18:00'],
        'saturday' => ['open' => '10:00', 'close' => '16:00'],
        'sunday' => null,
    ];

    foreach ($storeData as $index => $data) {
        $store = Store::firstOrCreate(
            ['phone' => $data['phone']],
            [
                'name' => $data['name'],
                'email' => $data['email'],
                'logo' => 'def.png',
                'cover_photo' => 'def.png',
                'latitude' => 35.6892 + ($index * 0.01),
                'longitude' => 51.3890 + ($index * 0.01),
                'zone_id' => $zone->id,
                'module_id' => $module->id,
                'status' => 1,
                'active' => 1,
                'vendor_id' => $vendor->id,
            ]
        );

        if (Schema::hasTable('beauty_salons')) {
            $salon = DB::table('beauty_salons')->where('store_id', $store->id)->first();
            if (!$salon) {
                $salonId = DB::table('beauty_salons')->insertGetId([
                    'store_id' => $store->id,
                    'zone_id' => $zone->id,
                    'business_type' => $data['salon']['business_type'],
                    'license_number' => $data['salon']['license_number'],
                    'license_expiry' => Carbon::now()->addYear(),
                    'verification_status' => $data['salon']['verification_status'],
                    'is_verified' => $data['salon']['is_verified'],
                    'is_featured' => $data['salon']['is_featured'],
                    'avg_rating' => $data['salon']['avg_rating'],
                    'total_bookings' => 0,
                    'total_reviews' => 0,
                    'working_hours' => json_encode($workingHours),
                    'holidays' => json_encode([]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $salons[] = ['id' => $salonId, 'store_id' => $store->id];
                echo "   Salon created: {$data['name']} (ID: {$salonId})\n";
            } else {
                $salons[] = ['id' => $salon->id, 'store_id' => $store->id];
                echo "   Salon exists: {$data['name']} (ID: {$salon->id})\n";
            }
        }
    }
    echo "\n";

    // 7. Create Service Categories
    echo "7. Creating Service Categories...\n";
    $categories = [];
    $categoryData = [
        ['name' => 'Hair Services', 'sort_order' => 1],
        ['name' => 'Facial Treatments', 'sort_order' => 2],
        ['name' => 'Nail Services', 'sort_order' => 3],
        ['name' => 'Massage', 'sort_order' => 4],
    ];

    foreach ($categoryData as $cat) {
        $category = DB::table('beauty_service_categories')->where('name', $cat['name'])->first();
        if (!$category) {
            $catId = DB::table('beauty_service_categories')->insertGetId([
                'parent_id' => null,
                'name' => $cat['name'],
                'image' => null,
                'status' => 1,
                'sort_order' => $cat['sort_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $categories[] = ['id' => $catId, 'name' => $cat['name']];
            echo "   Category created: {$cat['name']} (ID: {$catId})\n";
        } else {
            $categories[] = ['id' => $category->id, 'name' => $category->name];
            echo "   Category exists: {$cat['name']} (ID: {$category->id})\n";
        }
    }
    echo "\n";

    // 8. Create Services
    echo "8. Creating Services...\n";
    $serviceCount = 0;
    foreach ($salons as $salon) {
        foreach ($categories as $category) {
            $services = [
                ['name' => 'Haircut', 'description' => 'Professional haircut', 'price' => 100.00, 'duration' => 30],
                ['name' => 'Hair Color', 'description' => 'Full hair coloring', 'price' => 300.00, 'duration' => 120],
                ['name' => 'Facial Treatment', 'description' => 'Deep cleansing facial', 'price' => 200.00, 'duration' => 60],
                ['name' => 'Manicure', 'description' => 'Professional nail care', 'price' => 80.00, 'duration' => 45],
                ['name' => 'Massage', 'description' => 'Relaxing massage', 'price' => 150.00, 'duration' => 60],
            ];

            foreach ($services as $service) {
                $existing = DB::table('beauty_services')
                    ->where('salon_id', $salon['id'])
                    ->where('category_id', $category['id'])
                    ->where('name', $service['name'])
                    ->first();
                
                if (!$existing) {
                    DB::table('beauty_services')->insert([
                        'salon_id' => $salon['id'],
                        'category_id' => $category['id'],
                        'name' => $service['name'],
                        'description' => $service['description'],
                        'duration_minutes' => $service['duration'],
                        'price' => $service['price'],
                        'image' => null,
                        'status' => 1,
                        'service_type' => 'service',
                        'consultation_credit_percentage' => 0,
                        'staff_ids' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $serviceCount++;
                }
            }
        }
    }
    echo "   Created {$serviceCount} services\n\n";

    // 9. Create Staff
    echo "9. Creating Staff...\n";
    $staffCount = 0;
    if (Schema::hasTable('beauty_staff')) {
        $staffNames = ['Sarah Johnson', 'Emma Williams', 'Olivia Brown', 'Sophia Davis'];
        
        foreach ($salons as $salon) {
            foreach ($staffNames as $name) {
                $existing = DB::table('beauty_staff')
                    ->where('salon_id', $salon['id'])
                    ->where('name', $name)
                    ->first();
                
                if (!$existing) {
                    DB::table('beauty_staff')->insert([
                        'salon_id' => $salon['id'],
                        'name' => $name,
                        'phone' => '0912' . rand(1000000, 9999999),
                        'email' => strtolower(explode(' ', $name)[0]) . '@salon.com',
                        'specializations' => json_encode(['haircut', 'styling']),
                        'working_hours' => json_encode($workingHours),
                        'breaks' => json_encode([
                            ['start' => '13:00', 'end' => '14:00', 'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']]
                        ]),
                        'days_off' => json_encode([]),
                        'status' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $staffCount++;
                }
            }
        }
        echo "   Created {$staffCount} staff members\n\n";
    }

    // 10. Create Bookings
    echo "10. Creating Bookings...\n";
    $bookingCount = 0;
    if (Schema::hasTable('beauty_bookings') && !empty($salons)) {
        $salon = $salons[0];
        $service = DB::table('beauty_services')->where('salon_id', $salon['id'])->first();
        
        if ($service) {
            $bookingDates = [
                Carbon::now()->addDays(1)->format('Y-m-d'),
                Carbon::now()->addDays(2)->format('Y-m-d'),
                Carbon::now()->addDays(3)->format('Y-m-d'),
            ];

            foreach ($bookingDates as $date) {
                $existing = DB::table('beauty_bookings')
                    ->where('user_id', $user->id)
                    ->where('salon_id', $salon['id'])
                    ->where('booking_date', $date)
                    ->first();
                
                if (!$existing) {
                    // Get actual columns from table
                    $columns = Schema::getColumnListing('beauty_bookings');
                    $bookingData = [
                        'user_id' => $user->id,
                        'salon_id' => $salon['id'],
                        'service_id' => $service->id,
                        'staff_id' => null,
                        'booking_date' => $date,
                        'booking_time' => '10:00:00',
                        'booking_date_time' => "{$date} 10:00:00",
                        'total_amount' => $service->price * 1.02,
                        'status' => 'confirmed',
                        'payment_status' => 'paid',
                        'payment_method' => 'digital_payment',
                        'booking_reference' => 'BEAUTY-' . str_pad(100000 + $bookingCount, 6, '0', STR_PAD_LEFT),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    // Add optional fields if they exist
                    if (in_array('base_price', $columns)) {
                        $bookingData['base_price'] = $service->price;
                    }
                    if (in_array('service_fee', $columns)) {
                        $bookingData['service_fee'] = $service->price * 0.02;
                    }
                    if (in_array('tax_amount', $columns)) {
                        $bookingData['tax_amount'] = 0;
                    }
                    if (in_array('discount', $columns)) {
                        $bookingData['discount'] = 0;
                    }
                    
                    $bookingId = DB::table('beauty_bookings')->insertGetId($bookingData);
                    $bookingCount++;
                }
            }
        }
        echo "   Created {$bookingCount} bookings\n\n";
    }

    // 11. Create Reviews
    echo "11. Creating Reviews...\n";
    $reviewCount = 0;
    if (Schema::hasTable('beauty_reviews') && !empty($salons)) {
        $salon = $salons[0];
        $booking = DB::table('beauty_bookings')->where('user_id', $user->id)->where('salon_id', $salon['id'])->first();
        
        if ($booking) {
            $existing = DB::table('beauty_reviews')
                ->where('booking_id', $booking->id)
                ->first();
            
            if (!$existing) {
                $reviewData = [
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'salon_id' => $salon['id'],
                    'rating' => 5,
                    'comment' => 'Excellent service! Very professional staff.',
                    'status' => 'approved',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Add service_id if column exists
                $columns = Schema::getColumnListing('beauty_reviews');
                if (in_array('service_id', $columns) && isset($booking->service_id)) {
                    $reviewData['service_id'] = $booking->service_id;
                }
                
                DB::table('beauty_reviews')->insert($reviewData);
                $reviewCount++;
            }
        }
        echo "   Created {$reviewCount} reviews\n\n";
    }

    DB::commit();
    
    echo "✅ Complete test data seeding completed successfully!\n";
    echo "✅ داده‌های تست کامل با موفقیت ایجاد شدند!\n\n";
    
    echo "Summary:\n";
    echo "- Module ID: {$module->id}\n";
    echo "- Zone ID: {$zone->id}\n";
    echo "- User ID: {$user->id} (test@6ammart.com)\n";
    echo "- Vendor ID: {$vendor->id} (beauty@salon.com)\n";
    echo "- Salons: " . count($salons) . "\n";
    echo "- Categories: " . count($categories) . "\n";
    echo "- Services: {$serviceCount}\n";
    echo "- Staff: {$staffCount}\n";
    echo "- Bookings: {$bookingCount}\n";
    echo "- Reviews: {$reviewCount}\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

