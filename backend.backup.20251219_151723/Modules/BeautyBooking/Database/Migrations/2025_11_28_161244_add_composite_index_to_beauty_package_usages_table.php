<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add Composite Index to Beauty Package Usages Table
 * افزودن ایندکس ترکیبی به جدول استفاده از پکیج‌ها
 *
 * Adds composite index on (package_id, user_id, session_number) for efficient queries
 * در trackPackageUsage() method
 */
class AddCompositeIndexToBeautyPackageUsagesTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::table('beauty_package_usages', function (Blueprint $table) {
            // Add composite index for efficient session number queries
            // افزودن ایندکس ترکیبی برای کوئری‌های کارآمد شماره جلسه
            // This index is used in trackPackageUsage() to find the last session number
            // این ایندکس در trackPackageUsage() برای یافتن شماره جلسه آخر استفاده می‌شود
            $table->index(['package_id', 'user_id', 'session_number'], 'idx_beauty_package_usages_package_user_session');
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
        Schema::table('beauty_package_usages', function (Blueprint $table) {
            $table->dropIndex('idx_beauty_package_usages_package_user_session');
        });
    }
}
