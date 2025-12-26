<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\AdminRole;
use Modules\BeautyBooking\Entities\BeautyServiceCategory;
use Modules\BeautyBooking\Entities\BeautyCommissionSetting;
use Modules\BeautyBooking\Entities\BeautyBadge;

/**
 * Beauty Booking Database Seeder
 * Seeder پایگاه داده ماژول رزرو زیبایی
 *
 * Seeds default categories, commission settings, permissions, and badges
 * Seed کردن دسته‌بندی‌ها، تنظیمات کمیسیون، مجوزها و نشان‌ها
 */
class BeautyBookingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * اجرای seedها
     *
     * @return void
     */
    public function run()
    {
        // Seed default service categories
        // Seed کردن دسته‌بندی‌های خدمت پیش‌فرض
        $this->seedServiceCategories();

        // Seed default commission settings
        // Seed کردن تنظیمات کمیسیون پیش‌فرض
        $this->seedCommissionSettings();

        // Seed admin permissions
        // Seed کردن مجوزهای ادمین
        $this->seedAdminPermissions();

        // Seed badge definitions (documentation only, badges are auto-calculated)
        // Seed کردن تعاریف نشان‌ها (فقط مستندات، نشان‌ها به صورت خودکار محاسبه می‌شوند)
        // Note: Badges are auto-calculated by BeautyBadgeService, no seeding needed
        // توجه: نشان‌ها به صورت خودکار توسط BeautyBadgeService محاسبه می‌شوند، نیازی به seed نیست
    }

    /**
     * Seed service categories
     * Seed کردن دسته‌بندی‌های خدمت
     *
     * @return void
     */
    private function seedServiceCategories(): void
    {
        $categories = [
            [
                'name' => 'Hair Services',
                'parent_id' => null,
                'status' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Haircut',
                'parent_id' => null, // Will be set after parent creation
                'status' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'Hair Color',
                'parent_id' => null,
                'status' => 1,
                'sort_order' => 3,
            ],
            [
                'name' => 'Facial & Skin Care',
                'parent_id' => null,
                'status' => 1,
                'sort_order' => 4,
            ],
            [
                'name' => 'Massage',
                'parent_id' => null,
                'status' => 1,
                'sort_order' => 5,
            ],
            [
                'name' => 'Nail Services',
                'parent_id' => null,
                'status' => 1,
                'sort_order' => 6,
            ],
            [
                'name' => 'Makeup',
                'parent_id' => null,
                'status' => 1,
                'sort_order' => 7,
            ],
            [
                'name' => 'Medical Services',
                'parent_id' => null,
                'status' => 1,
                'sort_order' => 8,
            ],
        ];

        $parentCategories = [];
        foreach ($categories as $category) {
            $parentId = $category['parent_id'];
            unset($category['parent_id']);
            
            $created = BeautyServiceCategory::create($category);
            
            // Store parent categories for subcategories
            // ذخیره دسته‌بندی‌های والد برای زیرمجموعه‌ها
            if (in_array($created->name, ['Hair Services', 'Facial & Skin Care'])) {
                $parentCategories[$created->name] = $created->id;
            }
        }

        // Create subcategories
        // ایجاد زیرمجموعه‌ها
        if (isset($parentCategories['Hair Services'])) {
            $hairCutId = BeautyServiceCategory::where('name', 'Haircut')->first()->id ?? null;
            $hairColorId = BeautyServiceCategory::where('name', 'Hair Color')->first()->id ?? null;
            
            if ($hairCutId) {
                BeautyServiceCategory::where('id', $hairCutId)->update(['parent_id' => $parentCategories['Hair Services']]);
            }
            if ($hairColorId) {
                BeautyServiceCategory::where('id', $hairColorId)->update(['parent_id' => $parentCategories['Hair Services']]);
            }
        }
    }

    /**
     * Seed commission settings
     * Seed کردن تنظیمات کمیسیون
     *
     * @return void
     */
    private function seedCommissionSettings(): void
    {
        // Default commission settings
        // تنظیمات کمیسیون پیش‌فرض
        $settings = [
            [
                'service_category_id' => null,
                'salon_level' => null,
                'commission_percentage' => 10.00,
                'min_commission' => 0,
                'max_commission' => null,
                'status' => 1,
            ],
            [
                'service_category_id' => null,
                'salon_level' => 'salon',
                'commission_percentage' => 12.00,
                'min_commission' => 0,
                'max_commission' => null,
                'status' => 1,
            ],
            [
                'service_category_id' => null,
                'salon_level' => 'clinic',
                'commission_percentage' => 15.00,
                'min_commission' => 0,
                'max_commission' => null,
                'status' => 1,
            ],
        ];

        foreach ($settings as $setting) {
            // Check if setting already exists to avoid duplicates
            // بررسی وجود تنظیم برای جلوگیری از تکرار
            $exists = BeautyCommissionSetting::where('service_category_id', $setting['service_category_id'])
                ->where('salon_level', $setting['salon_level'])
                ->exists();
            
            if (!$exists) {
                BeautyCommissionSetting::create($setting);
            }
        }
    }

    /**
     * Seed admin permissions for Beauty Booking module
     * Seed کردن مجوزهای ادمین برای ماژول رزرو زیبایی
     *
     * @return void
     */
    private function seedAdminPermissions(): void
    {
        // Get all admin roles
        // دریافت تمام نقش‌های ادمین
        $roles = AdminRole::all();

        // Default Beauty Booking module permissions
        // مجوزهای پیش‌فرض ماژول رزرو زیبایی
        $beautyBookingModules = [
            'beauty_salon',
            'beauty_category',
            'beauty_booking',
            'beauty_review',
            'beauty_package',
            'beauty_gift_card',
            'beauty_retail',
            'beauty_loyalty',
            'beauty_subscription',
            'beauty_commission',
            'beauty_report',
        ];

        foreach ($roles as $role) {
            // Skip super admin (role_id = 1) as they have all permissions
            // رد کردن super admin (role_id = 1) چون تمام مجوزها را دارد
            if ($role->id == 1) {
                continue;
            }

            // Get current modules
            // دریافت ماژول‌های فعلی
            $currentModules = json_decode($role->modules ?? '[]', true);
            if (!is_array($currentModules)) {
                $currentModules = [];
            }

            // Add Beauty Booking modules if not already present
            // افزودن ماژول‌های رزرو زیبایی در صورت عدم وجود
            $updated = false;
            foreach ($beautyBookingModules as $module) {
                if (!in_array($module, $currentModules)) {
                    $currentModules[] = $module;
                    $updated = true;
                }
            }

            // Update role if modules were added
            // به‌روزرسانی نقش در صورت افزودن ماژول‌ها
            if ($updated) {
                $role->update(['modules' => json_encode($currentModules)]);
            }
        }
    }
}

