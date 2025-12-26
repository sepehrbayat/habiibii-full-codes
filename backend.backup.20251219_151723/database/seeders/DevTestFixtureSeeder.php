<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DevTestFixtureSeeder extends Seeder
{
    /**
     * Seed minimal dev/test fixtures for beauty module
     * داده‌های پایه برای محیط توسعه/تست ماژول بیوتی
     *
     * @return void
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();

            // Module
            $moduleId = DB::table('modules')->where('module_type', 'beauty')->value('id');
            if (!$moduleId) {
                $moduleId = DB::table('modules')->insertGetId([
                    'module_name' => 'beauty',
                    'module_type' => 'beauty',
                    'status' => 1,
                    'stores_count' => 0,
                    'theme_id' => 1,
                    'all_zone_service' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Zone (id = 1 if absent)
            $zoneId = DB::table('zones')->where('id', 1)->value('id');
            if (!$zoneId) {
                $zoneId = DB::table('zones')->insertGetId([
                    'id' => 1,
                    'name' => 'Test Zone',
                    'display_name' => 'Test Zone',
                    'status' => 1,
                    'cash_on_delivery' => 1,
                    'digital_payment' => 1,
                    'offline_payment' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Store
            $storeName = 'Test Beauty Store';
            $storeId = DB::table('stores')->where('name', $storeName)->value('id');
            $defaultLatLng = $this->defaultLocation();
            $storeData = [
                'zone_id' => $zoneId,
                'module_id' => $moduleId,
                'name' => $storeName,
                'phone' => '0000000000',
                'email' => 'beauty-store@example.com',
                'address' => 'Test Address',
                'latitude' => (string) $defaultLatLng['lat'],
                'longitude' => (string) $defaultLatLng['lng'],
                'schedule_order' => 1,
                'status' => 1,
                'active' => 1,
                'delivery' => 1,
                'take_away' => 1,
                'store_business_model' => 'commission',
                'slug' => Str::slug($storeName),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($storeId) {
                DB::table('stores')->where('id', $storeId)->update($storeData);
            } else {
                $storeId = DB::table('stores')->insertGetId($storeData);
            }

            // Store modules pivot
            if (Schema::hasTable('store_modules')) {
                DB::table('store_modules')->updateOrInsert(
                    ['store_id' => $storeId, 'module_id' => $moduleId],
                    ['status' => 1, 'created_at' => $now, 'updated_at' => $now]
                );
            }

            // Category
            $categoryName = 'Beauty Category';
            $categoryId = DB::table('categories')->where('name', $categoryName)->value('id');
            if (!$categoryId) {
                $categoryId = DB::table('categories')->insertGetId([
                    'name' => $categoryName,
                    'parent_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Item
            $itemName = 'Beauty Starter Item';
            $itemId = DB::table('items')->where('name', $itemName)->value('id');
            if ($itemId) {
                DB::table('items')->where('id', $itemId)->update([
                    'category_id' => $categoryId,
                    'module_id' => $moduleId,
                    'store_id' => $storeId,
                    'status' => 1,
                    'price' => 100,
                    'discount' => 0,
                    'recommended' => 0,
                    'organic' => 0,
                    'is_approved' => 1,
                    'is_halal' => 0,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('items')->insert([
                    'category_id' => $categoryId,
                    'module_id' => $moduleId,
                    'store_id' => $storeId,
                    'status' => 1,
                    'price' => 100,
                    'discount' => 0,
                    'name' => $itemName,
                    'slug' => Str::slug($itemName . '-' . Str::random(4)),
                    'recommended' => 0,
                    'organic' => 0,
                    'is_approved' => 1,
                    'is_halal' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Users
            $customers = [
                ['f_name' => 'John', 'l_name' => 'Customer', 'email' => 'john@customer.com'],
                ['f_name' => 'Jane', 'l_name' => 'Customer', 'email' => 'jane@customer.com'],
            ];

            $password = Hash::make('12345678');
            $firstUserId = null;

            foreach ($customers as $customer) {
                $existingId = DB::table('users')->where('email', $customer['email'])->value('id');
                if ($existingId) {
                    DB::table('users')->where('id', $existingId)->update([
                        'f_name' => $customer['f_name'],
                        'l_name' => $customer['l_name'],
                        'status' => 1,
                        'login_medium' => 'email',
                        'cm_firebase_token' => null,
                        'password' => $password,
                        'updated_at' => $now,
                    ]);
                    $userId = $existingId;
                } else {
                    $userId = DB::table('users')->insertGetId([
                        'f_name' => $customer['f_name'],
                        'l_name' => $customer['l_name'],
                        'email' => $customer['email'],
                        'status' => 1,
                        'login_medium' => 'email',
                        'cm_firebase_token' => null,
                        'password' => $password,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                if (!$firstUserId) {
                    $firstUserId = $userId;
                }
            }

            // Default address for first user
            if ($firstUserId && Schema::hasTable('customer_addresses')) {
                $defaultLatLng = $this->defaultLocation();
                DB::table('customer_addresses')->updateOrInsert(
                    ['user_id' => $firstUserId],
                    [
                        'contact_person_name' => 'John Customer',
                        'contact_person_number' => '0000000000',
                        'address_type' => 'home',
                        'address' => 'Test Address',
                        'floor' => null,
                        'road' => null,
                        'house' => null,
                        'longitude' => (string) $defaultLatLng['lng'],
                        'latitude' => (string) $defaultLatLng['lat'],
                        'zone_id' => $zoneId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            // Test banner (optional)
            if (Schema::hasTable('banners')) {
                $bannerTitle = 'Test Banner';
                $exists = DB::table('banners')->where('title', $bannerTitle)->exists();
                if (!$exists) {
                    DB::table('banners')->insert([
                        'title' => $bannerTitle,
                        'image' => null,
                        'data' => json_encode(['action' => 'none']),
                        'type' => 'default',
                        'zone_id' => $zoneId,
                        'module_id' => $moduleId,
                        'store_id' => $storeId,
                        'item_id' => $itemId ?? null,
                        'status' => 1,
                        'start_date' => null,
                        'end_date' => null,
                        'start_time' => null,
                        'end_time' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        });
    }

    /**
     * Get default lat/lng from settings or fallback
     * دریافت lat/lng پیش‌فرض از تنظیمات یا مقدار پیش‌فرض
     *
     * @return array{lat: float, lng: float}
     */
    private function defaultLocation(): array
    {
        $fallback = ['lat' => 23.757989, 'lng' => 90.360587];

        $setting = DB::table('business_settings')
            ->where('key', 'default_location')
            ->value('value');

        if ($setting) {
            $decoded = json_decode($setting, true);
            if (is_array($decoded) && isset($decoded['lat'], $decoded['lng'])) {
                return [
                    'lat' => (float) $decoded['lat'],
                    'lng' => (float) $decoded['lng'],
                ];
            }
        }

        return $fallback;
    }
}

