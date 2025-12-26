<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Loyalty Campaigns Table Migration
 * Migration برای ایجاد جدول کمپین‌های وفاداری
 */
class CreateBeautyLoyaltyCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_loyalty_campaigns', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('salon_id')
                ->nullable()
                ->constrained('beauty_salons')
                ->onDelete('cascade');
            
            // Campaign details
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['points', 'discount', 'cashback', 'gift_card'])->default('points');
            
            // Campaign rules
            $table->json('rules')->nullable(); // Flexible rules structure
            // Example: {"min_purchase": 100000, "points_per_currency": 1, "discount_percentage": 10}
            
            // Validity
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Commission settings
            $table->decimal('commission_percentage', 5, 2)->default(0.00);
            $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage');
            
            // Statistics
            $table->integer('total_participants')->default(0);
            $table->integer('total_redeemed')->default(0);
            $table->decimal('total_revenue', 23, 8)->default(0.00);
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('salon_id', 'idx_beauty_loyalty_campaigns_salon_id');
            $table->index('is_active', 'idx_beauty_loyalty_campaigns_is_active');
            $table->index(['start_date', 'end_date'], 'idx_beauty_loyalty_campaigns_dates');
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
        Schema::dropIfExists('beauty_loyalty_campaigns');
    }
}

