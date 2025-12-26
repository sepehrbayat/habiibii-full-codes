<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Loyalty Points Table Migration
 * Migration برای ایجاد جدول امتیازهای وفاداری
 */
class CreateBeautyLoyaltyPointsTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_loyalty_points', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('salon_id')
                ->nullable()
                ->constrained('beauty_salons')
                ->onDelete('cascade');
            $table->foreignId('campaign_id')
                ->nullable()
                ->constrained('beauty_loyalty_campaigns')
                ->onDelete('set null');
            $table->foreignId('booking_id')
                ->nullable()
                ->constrained('beauty_bookings')
                ->onDelete('set null');
            
            // Points details
            $table->integer('points')->default(0); // Can be positive (earned) or negative (redeemed)
            $table->enum('type', ['earned', 'redeemed', 'expired', 'adjusted'])->default('earned');
            $table->text('description')->nullable();
            
            // Expiry
            $table->date('expires_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('user_id', 'idx_beauty_loyalty_points_user_id');
            $table->index('salon_id', 'idx_beauty_loyalty_points_salon_id');
            $table->index('campaign_id', 'idx_beauty_loyalty_points_campaign_id');
            $table->index('booking_id', 'idx_beauty_loyalty_points_booking_id');
            $table->index(['user_id', 'salon_id'], 'idx_beauty_loyalty_points_user_salon');
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
        Schema::dropIfExists('beauty_loyalty_points');
    }
}

