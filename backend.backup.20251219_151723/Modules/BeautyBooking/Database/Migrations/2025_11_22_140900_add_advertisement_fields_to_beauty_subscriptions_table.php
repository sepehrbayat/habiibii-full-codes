<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add Advertisement Fields to Beauty Subscriptions Table Migration
 * Migration برای افزودن فیلدهای تبلیغات به جدول اشتراک‌ها
 */
class AddAdvertisementFieldsToBeautySubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        // Normalize any legacy values to avoid enum truncation
        // نرمال‌سازی مقادیر قدیمی برای جلوگیری از حذف داده در تغییر enum
        DB::table('beauty_subscriptions')
            ->whereNotIn('subscription_type', ['featured_listing', 'boost_ads', 'banner_ads', 'dashboard_subscription'])
            ->update(['subscription_type' => 'featured_listing']);

        // Update enum outside the Schema::table transaction-safe callback
        // به‌روزرسانی enum خارج از کال‌بک Schema::table برای حفظ تراکنش ایمن
        DB::statement("ALTER TABLE beauty_subscriptions MODIFY subscription_type ENUM('featured_listing', 'boost_ads', 'banner_ads', 'dashboard_subscription') DEFAULT 'featured_listing'");

        Schema::table('beauty_subscriptions', function (Blueprint $table) {
            $table->string('ad_position', 50)
                ->nullable()
                ->after('subscription_type')
                ->comment('Ad position: homepage, category_page, search_results (for banner ads)');
            
            $table->string('banner_image', 255)
                ->nullable()
                ->after('ad_position')
                ->comment('Banner image path (for banner ads)');
            
            $table->index('ad_position', 'idx_beauty_subscriptions_ad_position');
        });
    }

    /**
     * Reverse the migrations.
     * برگشت migration
     *
     * @return void
     */
    public function down()
    {
        Schema::table('beauty_subscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_beauty_subscriptions_ad_position');
            $table->dropColumn(['ad_position', 'banner_image']);
        });

        // Revert enum definition after structural rollback
        // بازگرداندن تعریف enum پس از حذف ستون‌ها
        DB::statement("ALTER TABLE beauty_subscriptions MODIFY subscription_type ENUM('featured_listing', 'boost_ads', 'banner_ads', 'dashboard_subscription') DEFAULT 'featured_listing'");
    }
}

