<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Vendor;
use App\Models\Store;
use App\Models\Module;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "Creating test vendor with multiple stores and different module access...\n\n";

// Get available modules
$groceryModule = Module::where('module_type', 'grocery')->first();
$beautyModule = Module::where('module_type', 'beauty')->first();

if (!$groceryModule || !$beautyModule) {
    echo "Error: Required modules not found!\n";
    exit(1);
}

// Get a zone
$zone = Zone::first();
if (!$zone) {
    echo "Error: No zones found!\n";
    exit(1);
}

// Create vendor
$vendor = Vendor::create([
    'f_name' => 'Test',
    'l_name' => 'MultiModule',
    'email' => 'test.multimodule@example.com',
    'phone' => '+1234567890',
    'password' => Hash::make('password123'),
    'status' => 1, // Active
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "✓ Created vendor: {$vendor->f_name} {$vendor->l_name} (ID: {$vendor->id})\n";
echo "  Email: {$vendor->email}\n";
echo "  Password: password123\n\n";

// Store 1: Primary = Grocery, with access to Beauty
$store1 = Store::create([
    'name' => 'Grocery Store with Beauty Access',
    'phone' => '+1234567891',
    'email' => 'grocery.beauty@example.com',
    'vendor_id' => $vendor->id,
    'zone_id' => $zone->id,
    'module_id' => $groceryModule->id, // Primary module
    'address' => '123 Main Street, Test City',
    'latitude' => '40.7128',
    'longitude' => '-74.0060',
    'delivery_time' => '30-40 min',
    'minimum_order' => 10.00,
    'comission' => 10.00,
    'status' => 1, // Active
    'active' => 1,
    'schedule_order' => 1,
    'delivery' => 1,
    'take_away' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]);

// Add beauty module access to store 1
DB::table('store_modules')->insert([
    'store_id' => $store1->id,
    'module_id' => $beautyModule->id,
    'status' => true,
    'created_at' => now(),
    'updated_at' => now(),
]);

// Add primary module to store_modules (for consistency)
DB::table('store_modules')->insert([
    'store_id' => $store1->id,
    'module_id' => $groceryModule->id,
    'status' => true,
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "✓ Created Store 1: {$store1->name} (ID: {$store1->id})\n";
echo "  Primary Module: Grocery\n";
echo "  Additional Access: Beauty\n\n";

// Store 2: Primary = Beauty, with access to Grocery
$store2 = Store::create([
    'name' => 'Beauty Salon with Grocery Access',
    'phone' => '+1234567892',
    'email' => 'beauty.grocery@example.com',
    'vendor_id' => $vendor->id,
    'zone_id' => $zone->id,
    'module_id' => $beautyModule->id, // Primary module
    'address' => '456 Beauty Avenue, Test City',
    'latitude' => '40.7130',
    'longitude' => '-74.0062',
    'delivery_time' => '30-40 min',
    'minimum_order' => 20.00,
    'comission' => 15.00,
    'status' => 1, // Active
    'active' => 1,
    'schedule_order' => 1,
    'delivery' => 1,
    'take_away' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]);

// Add grocery module access to store 2
DB::table('store_modules')->insert([
    'store_id' => $store2->id,
    'module_id' => $groceryModule->id,
    'status' => true,
    'created_at' => now(),
    'updated_at' => now(),
]);

// Add primary module to store_modules (for consistency)
DB::table('store_modules')->insert([
    'store_id' => $store2->id,
    'module_id' => $beautyModule->id,
    'status' => true,
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "✓ Created Store 2: {$store2->name} (ID: {$store2->id})\n";
echo "  Primary Module: Beauty\n";
echo "  Additional Access: Grocery\n\n";

// Store 3: Primary = Grocery, Beauty access DISABLED (to test disabled access)
$store3 = Store::create([
    'name' => 'Grocery Store Only',
    'phone' => '+1234567893',
    'email' => 'grocery.only@example.com',
    'vendor_id' => $vendor->id,
    'zone_id' => $zone->id,
    'module_id' => $groceryModule->id, // Primary module
    'address' => '789 Grocery Lane, Test City',
    'latitude' => '40.7140',
    'longitude' => '-74.0064',
    'delivery_time' => '30-40 min',
    'minimum_order' => 15.00,
    'comission' => 12.00,
    'status' => 1, // Active
    'active' => 1,
    'schedule_order' => 1,
    'delivery' => 1,
    'take_away' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]);

// Add primary module to store_modules (for consistency)
DB::table('store_modules')->insert([
    'store_id' => $store3->id,
    'module_id' => $groceryModule->id,
    'status' => true,
    'created_at' => now(),
    'updated_at' => now(),
]);

// Add beauty module access but DISABLED
DB::table('store_modules')->insert([
    'store_id' => $store3->id,
    'module_id' => $beautyModule->id,
    'status' => false, // DISABLED
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "✓ Created Store 3: {$store3->name} (ID: {$store3->id})\n";
echo "  Primary Module: Grocery\n";
echo "  Beauty Access: DISABLED (for testing)\n\n";

// Verify the setup
echo "=== Verification ===\n\n";

$stores = Store::where('vendor_id', $vendor->id)->get();
foreach ($stores as $store) {
    echo "Store: {$store->name} (ID: {$store->id})\n";
    echo "  Primary Module: {$store->module->module_type}\n";
    echo "  Has Grocery Access: " . ($store->hasModuleAccess('grocery') ? 'Yes' : 'No') . "\n";
    echo "  Has Beauty Access: " . ($store->hasModuleAccess('beauty') ? 'Yes' : 'No') . "\n";
    echo "  Accessible Modules: " . implode(', ', $store->getAccessibleModuleTypes()) . "\n";
    echo "\n";
}

echo "=== Login Credentials ===\n\n";
echo "Email: {$vendor->email}\n";
echo "Password: password123\n";
echo "Vendor ID: {$vendor->id}\n\n";

echo "=== Store Selection ===\n\n";
echo "When logging in, you can select from these stores:\n";
foreach ($stores as $index => $store) {
    echo ($index + 1) . ". {$store->name} (ID: {$store->id})\n";
    echo "   - Primary: {$store->module->module_type}\n";
    echo "   - Access: " . implode(', ', $store->getAccessibleModuleTypes()) . "\n";
}

echo "\n=== Testing Scenarios ===\n\n";
echo "1. Login and select Store 1 (Grocery + Beauty):\n";
echo "   - Should see module switcher in header\n";
echo "   - Should see both Grocery and Beauty sections in sidebar\n";
echo "   - Can switch between Grocery and Beauty dashboards\n\n";

echo "2. Login and select Store 2 (Beauty + Grocery):\n";
echo "   - Should see module switcher in header\n";
echo "   - Should see both Beauty and Grocery sections in sidebar\n";
echo "   - Can switch between Beauty and Grocery dashboards\n\n";

echo "3. Login and select Store 3 (Grocery only):\n";
echo "   - Should NOT see module switcher (only 1 accessible module)\n";
echo "   - Should see only Grocery sections in sidebar\n";
echo "   - Should NOT see Beauty sections\n\n";

echo "Done!\n";

