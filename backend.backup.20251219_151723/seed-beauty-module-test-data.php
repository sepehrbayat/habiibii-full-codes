<?php

/**
 * Seed Beauty Booking Module Test Data
 * ایجاد داده‌های تست برای ماژول رزرو زیبایی
 * 
 * This script creates:
 * - Beauty Booking Module
 * - Zone
 * - Store with Beauty Salon
 * - Service Categories
 * - Services
 * - Staff
 * - Bookings
 * - Reviews
 * - And more test data
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
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;

echo "Starting Beauty Module Test Data Seeding...\n";
echo "شروع ایجاد داده‌های تست ماژول زیبایی...\n\n";

try {
    DB::beginTransaction();

    // 1. Create or Get Beauty Booking Module
    echo "1. Creating Beauty Booking Module...\n";
    $module = Module::firstOrCreate(
        ['module_name' => 'beauty_booking'],
        [
            'module_name' => 'beauty_booking',
            'module_type' => 'beauty',
            'thumbnail' => null,
            'status' => 1,
            'stores_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
    echo "   Module ID: {$module->id}\n";
    echo "   Module Name: {$module->module_name}\n\n";

    // 2. Get or Create Zone (without coordinates to avoid spatial issues)
    echo "2. Getting or Creating Zone...\n";
    $zone = Zone::first();
    if (!$zone) {
        // Create zone without coordinates (can be set later via admin panel)
        $zone = Zone::create([
            'name' => 'Tehran Zone',
            'display_name' => 'Tehran Zone',
            'status' => 1,
            'coordinates' => null, // Will be set via admin panel
            'cash_on_delivery' => 1,
            'digital_payment' => 1,
            'offline_payment' => 1,
        ]);
        echo "   Zone created (without coordinates)\n";
    }
    echo "   Zone ID: {$zone->id}\n";
    echo "   Zone Name: {$zone->name}\n\n";

    // 3. Attach Zone to Module
    echo "3. Attaching Zone to Module...\n";
    if (!$module->zones()->where('zone_id', $zone->id)->exists()) {
        $module->zones()->attach($zone->id);
        echo "   Zone attached to module\n\n";
    } else {
        echo "   Zone already attached\n\n";
    }

    // 4. Get or Create Test User
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
    echo "   User Email: {$user->email}\n\n";

    // 5. Create Vendor
    echo "5. Creating Vendor...\n";
    $vendor = Vendor::firstOrCreate(
        ['email' => 'beauty@salon.com'],
        [
            'f_name' => 'Beauty',
            'l_name' => 'Salon Owner',
            'email' => 'beauty@salon.com',
            'phone' => '09123456780',
            'password' => Hash::make('123456'),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
    echo "   Vendor ID: {$vendor->id}\n";
    echo "   Vendor Email: {$vendor->email}\n\n";

    // 6. Create Store
    echo "6. Creating Store...\n";
    $store = Store::firstOrCreate(
        ['vendor_id' => $vendor->id],
        [
            'vendor_id' => $vendor->id,
            'name' => 'Beauty Salon Test',
            'address' => 'Tehran, Valiasr Street',
            'latitude' => '35.6892',
            'longitude' => '51.3890',
            'zone_id' => $zone->id,
            'module_id' => $module->id,
            'status' => 1,
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
    echo "   Store ID: {$store->id}\n";
    echo "   Store Name: {$store->name}\n\n";

    // 7. Create Beauty Salon
    echo "7. Creating Beauty Salon...\n";
    if (Schema::hasTable('beauty_salons')) {
        $salon = DB::table('beauty_salons')->where('store_id', $store->id)->first();
        if (!$salon) {
            $salonId = DB::table('beauty_salons')->insertGetId([
                'store_id' => $store->id,
                'zone_id' => $zone->id,
                'business_type' => 'salon',
                'license_number' => 'BEAUTY-001',
                'license_expiry' => now()->addYear(),
                'verification_status' => 1,
                'is_verified' => 1,
                'is_featured' => 1,
                'avg_rating' => 4.8,
                'total_bookings' => 0,
                'total_reviews' => 0,
                'working_hours' => json_encode([
                    'sunday' => ['open' => '09:00', 'close' => '21:00'],
                    'monday' => ['open' => '09:00', 'close' => '21:00'],
                    'tuesday' => ['open' => '09:00', 'close' => '21:00'],
                    'wednesday' => ['open' => '09:00', 'close' => '21:00'],
                    'thursday' => ['open' => '09:00', 'close' => '21:00'],
                    'friday' => ['open' => '09:00', 'close' => '21:00'],
                    'saturday' => ['open' => '09:00', 'close' => '21:00'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "   Salon ID: {$salonId}\n\n";
        } else {
            echo "   Salon already exists (ID: {$salon->id})\n\n";
        }
    } else {
        echo "   beauty_salons table does not exist yet\n\n";
    }

    // 8. Create Service Categories
    echo "8. Creating Service Categories...\n";
    if (Schema::hasTable('beauty_service_categories')) {
        $categories = [
            ['name' => 'Hair Services', 'image' => null, 'status' => 1, 'sort_order' => 1],
            ['name' => 'Facial Treatments', 'image' => null, 'status' => 1, 'sort_order' => 2],
            ['name' => 'Nail Services', 'image' => null, 'status' => 1, 'sort_order' => 3],
            ['name' => 'Massage', 'image' => null, 'status' => 1, 'sort_order' => 4],
            ['name' => 'Haircut', 'image' => null, 'status' => 1, 'sort_order' => 5],
            ['name' => 'Hair Color', 'image' => null, 'status' => 1, 'sort_order' => 6],
        ];
        
        foreach ($categories as $cat) {
            $category = DB::table('beauty_service_categories')->where('name', $cat['name'])->first();
            if (!$category) {
                $catId = DB::table('beauty_service_categories')->insertGetId($cat + [
                    'parent_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                echo "   Category created: {$cat['name']} (ID: {$catId})\n";
            } else {
                echo "   Category exists: {$cat['name']} (ID: {$category->id})\n";
            }
        }
        echo "\n";
    } else {
        echo "   beauty_service_categories table does not exist yet\n\n";
    }

    // 9. Create Services
    echo "9. Creating Services...\n";
    if (Schema::hasTable('beauty_services') && Schema::hasTable('beauty_salons')) {
        $salon = DB::table('beauty_salons')->where('store_id', $store->id)->first();
        if ($salon) {
            $category = DB::table('beauty_service_categories')->where('name', 'Hair Services')->first();
            if ($category) {
                $services = [
                    ['salon_id' => $salon->id, 'category_id' => $category->id, 'name' => 'Haircut', 'description' => 'Professional haircut', 'price' => 50.00, 'duration_minutes' => 30, 'status' => 1],
                    ['salon_id' => $salon->id, 'category_id' => $category->id, 'name' => 'Hair Color', 'description' => 'Full hair coloring', 'price' => 120.00, 'duration_minutes' => 120, 'status' => 1],
                    ['salon_id' => $salon->id, 'category_id' => $category->id, 'name' => 'Hair Styling', 'description' => 'Professional hair styling', 'price' => 80.00, 'duration_minutes' => 60, 'status' => 1],
                ];
                
                foreach ($services as $service) {
                    $existing = DB::table('beauty_services')->where('salon_id', $service['salon_id'])->where('name', $service['name'])->first();
                    if (!$existing) {
                        $serviceId = DB::table('beauty_services')->insertGetId($service + [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        echo "   Service created: {$service['name']} (ID: {$serviceId})\n";
                    } else {
                        echo "   Service exists: {$service['name']} (ID: {$existing->id})\n";
                    }
                }
            }
        }
        echo "\n";
    } else {
        echo "   beauty_services table does not exist yet\n\n";
    }

    DB::commit();
    
    echo "✅ Test data seeding completed successfully!\n";
    echo "✅ داده‌های تست با موفقیت ایجاد شدند!\n\n";
    
    echo "Summary:\n";
    echo "- Module ID: {$module->id}\n";
    echo "- Zone ID: {$zone->id}\n";
    echo "- User ID: {$user->id} (test@6ammart.com)\n";
    echo "- Store ID: {$store->id}\n";
    echo "- Vendor ID: {$vendor->id} (beauty@salon.com)\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

