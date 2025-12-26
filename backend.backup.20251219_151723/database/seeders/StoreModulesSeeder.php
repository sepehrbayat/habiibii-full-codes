<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Store Modules Seeder
 * Seeder برای دسترسی ماژول‌های فروشگاه
 *
 * Populates store_modules table for existing stores
 * پر کردن جدول store_modules برای فروشگاه‌های موجود
 * Sets primary module as default accessible module
 * تنظیم ماژول اصلی به عنوان ماژول قابل دسترسی پیش‌فرض
 */
class StoreModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * اجرای seed
     *
     * @return void
     */
    public function run(): void
    {
        // Get all stores
        // دریافت تمام فروشگاه‌ها
        $stores = Store::whereNotNull('module_id')->get();

        foreach ($stores as $store) {
            // Check if entry already exists
            // بررسی اینکه آیا ورودی از قبل وجود دارد
            $exists = DB::table('store_modules')
                ->where('store_id', $store->id)
                ->where('module_id', $store->module_id)
                ->exists();

            if (!$exists) {
                // Insert primary module as accessible
                // درج ماژول اصلی به عنوان قابل دسترسی
                DB::table('store_modules')->insert([
                    'store_id' => $store->id,
                    'module_id' => $store->module_id,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Store modules seeded successfully.');
    }
}
