<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add Auto Renew Column to Beauty Subscriptions Table Migration
 * Migration برای افزودن ستون auto_renew به جدول اشتراک‌ها
 *
 * Allows subscriptions to be automatically renewed before expiration
 * اجازه می‌دهد اشتراک‌ها قبل از انقضا به طور خودکار تمدید شوند
 */
class AddAutoRenewToBeautySubscriptions extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('beauty_subscriptions', function (Blueprint $table) {
            // Auto-renew flag: if true, subscription will be automatically renewed before expiration
            // پرچم تمدید خودکار: در صورت true، اشتراک قبل از انقضا به طور خودکار تمدید می‌شود
            $table->boolean('auto_renew')->default(false)->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     * برگشت migration
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('beauty_subscriptions', function (Blueprint $table) {
            $table->dropColumn('auto_renew');
        });
    }
}

