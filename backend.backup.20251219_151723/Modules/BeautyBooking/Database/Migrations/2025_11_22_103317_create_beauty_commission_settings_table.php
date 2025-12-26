<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Commission Settings Table Migration
 * Migration برای ایجاد جدول تنظیمات کمیسیون
 */
class CreateBeautyCommissionSettingsTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_commission_settings', function (Blueprint $table) {
            $table->id();
            
            // Foreign key (nullable for global settings)
            $table->foreignId('service_category_id')
                ->nullable()
                ->constrained('beauty_service_categories')
                ->onDelete('cascade');
            
            // Commission settings
            $table->string('salon_level', 50)->nullable(); // salon, clinic, or null for all
            $table->decimal('commission_percentage', 5, 2)->default(0.00);
            $table->decimal('min_commission', 23, 8)->default(0.00);
            $table->decimal('max_commission', 23, 8)->nullable();
            $table->boolean('status')->default(1);
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('service_category_id', 'idx_beauty_commission_settings_category_id');
            $table->index('salon_level', 'idx_beauty_commission_settings_salon_level');
            $table->index('status', 'idx_beauty_commission_settings_status');
            $table->index(['service_category_id', 'salon_level'], 'idx_beauty_commission_settings_category_level');
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
        Schema::dropIfExists('beauty_commission_settings');
    }
}

