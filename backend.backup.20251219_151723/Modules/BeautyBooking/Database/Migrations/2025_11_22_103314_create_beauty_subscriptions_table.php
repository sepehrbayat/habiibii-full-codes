<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Subscriptions Table Migration
 * Migration برای ایجاد جدول اشتراک‌ها
 */
class CreateBeautySubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_subscriptions', function (Blueprint $table) {
            $table->id();
            
            // Foreign key
            $table->foreignId('salon_id')
                ->constrained('beauty_salons')
                ->onDelete('cascade');
            
            // Subscription details (aligned with advertisement types)
            // جزئیات اشتراک (منطبق با انواع تبلیغات)
            $table->enum('subscription_type', ['featured_listing', 'boost_ads', 'banner_ads', 'dashboard_subscription'])
                ->default('featured_listing');
            $table->integer('duration_days'); // Duration in days (7 or 30)
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('amount_paid', 23, 8)->default(0.00);
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('salon_id', 'idx_beauty_subscriptions_salon_id');
            $table->index('subscription_type', 'idx_beauty_subscriptions_subscription_type');
            $table->index('status', 'idx_beauty_subscriptions_status');
            $table->index('end_date', 'idx_beauty_subscriptions_end_date');
            $table->index(['salon_id', 'status'], 'idx_beauty_subscriptions_salon_status');
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
        Schema::dropIfExists('beauty_subscriptions');
    }
}

