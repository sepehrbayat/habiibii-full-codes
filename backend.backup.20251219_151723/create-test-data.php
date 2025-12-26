<?php

/**
 * Create Test Data for Beauty Booking Module
 * ایجاد داده‌های تست برای ماژول Beauty Booking
 * 
 * This script creates all necessary test data for testing the Beauty Booking module
 * این اسکریپت تمام داده‌های تست لازم برای تست ماژول Beauty Booking را ایجاد می‌کند
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Store;
use App\Models\Zone;
use App\Models\User;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyServiceCategory;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyStaff;
use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "Creating Test Data for Beauty Booking\n";
echo "ایجاد داده‌های تست برای Beauty Booking\n";
echo "========================================\n\n";

try {
    DB::beginTransaction();
    
    // Step 1: Get or create Store
    echo "Step 1: Getting/Creating Store...\n";
    echo "مرحله 1: دریافت/ایجاد فروشگاه...\n";
    
    $store = Store::first();
    if (!$store) {
        echo "⚠️  No store found. Creating test store...\n";
        echo "⚠️  فروشگاهی پیدا نشد. ایجاد فروشگاه تست...\n";
        
        // Create a minimal store
        $store = Store::create([
            'name' => 'Test Beauty Store',
            'email' => 'test@beautystore.com',
            'phone' => '09123456789',
            'status' => 1,
            'active' => 1,
            'vendor_id' => 1, // Assuming vendor exists
        ]);
        echo "✓ Created store: ID {$store->id}\n";
        echo "✓ فروشگاه ایجاد شد: ID {$store->id}\n";
    } else {
        echo "✓ Using existing store: ID {$store->id}\n";
        echo "✓ استفاده از فروشگاه موجود: ID {$store->id}\n";
    }
    echo "\n";
    
    // Step 2: Get or create Zone
    echo "Step 2: Getting/Creating Zone...\n";
    echo "مرحله 2: دریافت/ایجاد منطقه...\n";
    
    $zone = Zone::first();
    if (!$zone) {
        echo "⚠️  No zone found. Creating test zone...\n";
        echo "⚠️  منطقه‌ای پیدا نشد. ایجاد منطقه تست...\n";
        
        $zone = Zone::create([
            'name' => 'Test Zone',
            'status' => 1,
        ]);
        echo "✓ Created zone: ID {$zone->id}\n";
        echo "✓ منطقه ایجاد شد: ID {$zone->id}\n";
    } else {
        echo "✓ Using existing zone: ID {$zone->id}\n";
        echo "✓ استفاده از منطقه موجود: ID {$zone->id}\n";
    }
    echo "\n";
    
    // Step 3: Get or create User
    echo "Step 3: Getting/Creating User...\n";
    echo "مرحله 3: دریافت/ایجاد کاربر...\n";
    
    $user = User::first();
    if (!$user) {
        echo "⚠️  No user found. Creating test user...\n";
        echo "⚠️  کاربری پیدا نشد. ایجاد کاربر تست...\n";
        
        $user = User::create([
            'f_name' => 'Test',
            'l_name' => 'User',
            'email' => 'testuser@example.com',
            'phone' => '09111111111',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);
        echo "✓ Created user: ID {$user->id}\n";
        echo "✓ کاربر ایجاد شد: ID {$user->id}\n";
    } else {
        echo "✓ Using existing user: ID {$user->id}\n";
        echo "✓ استفاده از کاربر موجود: ID {$user->id}\n";
    }
    echo "\n";
    
    // Step 4: Create or get Beauty Salon
    echo "Step 4: Creating/Getting Beauty Salon...\n";
    echo "مرحله 4: ایجاد/دریافت سالن زیبایی...\n";
    
    $salon = BeautySalon::where('store_id', $store->id)->first();
    
    if (!$salon) {
        $workingHours = [
            'monday' => ['open' => '09:00', 'close' => '18:00'],
            'tuesday' => ['open' => '09:00', 'close' => '18:00'],
            'wednesday' => ['open' => '09:00', 'close' => '18:00'],
            'thursday' => ['open' => '09:00', 'close' => '18:00'],
            'friday' => ['open' => '09:00', 'close' => '18:00'],
            'saturday' => ['open' => '09:00', 'close' => '18:00'],
            'sunday' => ['open' => '10:00', 'close' => '16:00'],
        ];
        
        $salon = BeautySalon::create([
            'store_id' => $store->id,
            'zone_id' => $zone->id,
            'business_type' => 'salon',
            'license_number' => 'TEST-LICENSE-001',
            'verification_status' => 1, // Approved
            'is_verified' => true,
            'is_featured' => false,
            'working_hours' => $workingHours,
            'holidays' => [],
            'avg_rating' => 0.00,
            'total_bookings' => 0,
            'total_reviews' => 0,
        ]);
        echo "✓ Created salon: ID {$salon->id}\n";
        echo "✓ سالن ایجاد شد: ID {$salon->id}\n";
    } else {
        // Update to verified if not already
        if (!$salon->is_verified) {
            $salon->update([
                'verification_status' => 1,
                'is_verified' => true,
            ]);
            echo "✓ Updated salon to verified: ID {$salon->id}\n";
            echo "✓ سالن به تأیید شده به‌روزرسانی شد: ID {$salon->id}\n";
        } else {
            echo "✓ Using existing verified salon: ID {$salon->id}\n";
            echo "✓ استفاده از سالن تأیید شده موجود: ID {$salon->id}\n";
        }
    }
    echo "\n";
    
    // Step 5: Create Service Category
    echo "Step 5: Creating Service Category...\n";
    echo "مرحله 5: ایجاد دسته‌بندی خدمت...\n";
    
    $category = BeautyServiceCategory::where('name', 'Hair Services')->first();
    
    if (!$category) {
        $category = BeautyServiceCategory::create([
            'name' => 'Hair Services',
            'status' => 1,
            'sort_order' => 1,
        ]);
        echo "✓ Created category: ID {$category->id} - {$category->name}\n";
        echo "✓ دسته‌بندی ایجاد شد: ID {$category->id} - {$category->name}\n";
    } else {
        echo "✓ Using existing category: ID {$category->id} - {$category->name}\n";
        echo "✓ استفاده از دسته‌بندی موجود: ID {$category->id} - {$category->name}\n";
    }
    echo "\n";
    
    // Step 6: Create Service
    echo "Step 6: Creating Service...\n";
    echo "مرحله 6: ایجاد خدمت...\n";
    
    $service = BeautyService::where('salon_id', $salon->id)
        ->where('name', 'Haircut')
        ->first();
    
    if (!$service) {
        $service = BeautyService::create([
            'salon_id' => $salon->id,
            'category_id' => $category->id,
            'name' => 'Haircut',
            'description' => 'Professional haircut service',
            'duration_minutes' => 30,
            'price' => 100000.00, // 100,000 in smallest currency unit
            'status' => 1,
        ]);
        echo "✓ Created service: ID {$service->id} - {$service->name}\n";
        echo "✓ خدمت ایجاد شد: ID {$service->id} - {$service->name}\n";
    } else {
        echo "✓ Using existing service: ID {$service->id} - {$service->name}\n";
        echo "✓ استفاده از خدمت موجود: ID {$service->id} - {$service->name}\n";
    }
    echo "\n";
    
    // Step 7: Create Staff (optional)
    echo "Step 7: Creating Staff (optional)...\n";
    echo "مرحله 7: ایجاد کارمند (اختیاری)...\n";
    
    $staff = BeautyStaff::where('salon_id', $salon->id)->first();
    
    if (!$staff) {
        $staffWorkingHours = [
            'monday' => ['open' => '09:00', 'close' => '18:00'],
            'tuesday' => ['open' => '09:00', 'close' => '18:00'],
            'wednesday' => ['open' => '09:00', 'close' => '18:00'],
            'thursday' => ['open' => '09:00', 'close' => '18:00'],
            'friday' => ['open' => '09:00', 'close' => '18:00'],
            'saturday' => ['open' => '09:00', 'close' => '18:00'],
            'sunday' => null, // Day off
        ];
        
        $staff = BeautyStaff::create([
            'salon_id' => $salon->id,
            'name' => 'Test Staff Member',
            'email' => 'staff@test.com',
            'phone' => '09122222222',
            'status' => 1,
            'specializations' => ['Haircut', 'Styling'],
            'working_hours' => $staffWorkingHours,
            'breaks' => [],
            'days_off' => [],
        ]);
        echo "✓ Created staff: ID {$staff->id} - {$staff->name}\n";
        echo "✓ کارمند ایجاد شد: ID {$staff->id} - {$staff->name}\n";
    } else {
        echo "✓ Using existing staff: ID {$staff->id} - {$staff->name}\n";
        echo "✓ استفاده از کارمند موجود: ID {$staff->id} - {$staff->name}\n";
    }
    echo "\n";
    
    DB::commit();
    
    echo "========================================\n";
    echo "Test Data Created Successfully!\n";
    echo "داده‌های تست با موفقیت ایجاد شدند!\n";
    echo "========================================\n\n";
    
    echo "Summary:\n";
    echo "خلاصه:\n";
    echo "  Store ID: {$store->id}\n";
    echo "  Zone ID: {$zone->id}\n";
    echo "  User ID: {$user->id}\n";
    echo "  Salon ID: {$salon->id} (Verified: " . ($salon->is_verified ? 'YES' : 'NO') . ")\n";
    echo "  Category ID: {$category->id}\n";
    echo "  Service ID: {$service->id}\n";
    echo "  Staff ID: {$staff->id}\n";
    echo "\n";
    echo "Ready to test booking creation!\n";
    echo "آماده برای تست ایجاد رزرو!\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "❌ خطا: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

