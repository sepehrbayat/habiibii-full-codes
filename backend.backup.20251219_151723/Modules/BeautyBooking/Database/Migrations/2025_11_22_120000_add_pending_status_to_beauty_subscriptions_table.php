<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add pending status to beauty_subscriptions status enum
 * افزودن وضعیت pending به enum status در beauty_subscriptions
 */
class AddPendingStatusToBeautySubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up(): void
    {
        // Modify status enum to include 'pending'
        // تغییر enum status برای شامل کردن 'pending'
        DB::statement("ALTER TABLE `beauty_subscriptions` MODIFY `status` ENUM('pending', 'active', 'expired', 'cancelled') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     * برگشت migration
     *
     * @return void
     */
    public function down(): void
    {
        // Revert status enum to original values (without 'pending')
        // برگشت enum status به مقادیر اصلی (بدون 'pending')
        // Note: This will fail if there are any subscriptions with 'pending' status
        // توجه: این کار در صورت وجود اشتراک‌های با وضعیت 'pending' شکست می‌خورد
        DB::statement("ALTER TABLE `beauty_subscriptions` MODIFY `status` ENUM('active', 'expired', 'cancelled') DEFAULT 'active'");
    }
}

