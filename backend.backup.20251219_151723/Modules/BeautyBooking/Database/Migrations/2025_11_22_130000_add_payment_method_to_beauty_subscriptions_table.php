<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add payment_method column to beauty_subscriptions table
 * افزودن ستون payment_method به جدول beauty_subscriptions
 */
class AddPaymentMethodToBeautySubscriptionsTable extends Migration
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
            $table->string('payment_method', 50)->nullable()->after('amount_paid');
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
            $table->dropColumn('payment_method');
        });
    }
}

