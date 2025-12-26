<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Service Staff Pivot Table Migration
 * Migration برای ایجاد جدول pivot خدمات-کارمندان
 */
class CreateBeautyServiceStaffTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_service_staff', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('service_id')
                ->constrained('beauty_services')
                ->onDelete('cascade');
            $table->foreignId('staff_id')
                ->constrained('beauty_staff')
                ->onDelete('cascade');
            
            // Timestamps
            $table->timestamps();
            
            // Unique constraint to prevent duplicate assignments
            $table->unique(['service_id', 'staff_id'], 'idx_beauty_service_staff_unique');
            
            // Indexes
            $table->index('service_id', 'idx_beauty_service_staff_service_id');
            $table->index('staff_id', 'idx_beauty_service_staff_staff_id');
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
        Schema::dropIfExists('beauty_service_staff');
    }
}

