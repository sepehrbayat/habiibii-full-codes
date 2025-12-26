<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Staff Table Migration
 * Migration برای ایجاد جدول کارمندان سالن
 */
class CreateBeautyStaffTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_staff', function (Blueprint $table) {
            $table->id();
            
            // Foreign key
            $table->foreignId('salon_id')
                ->constrained('beauty_salons')
                ->onDelete('cascade');
            
            // Personal information
            $table->string('name', 255);
            $table->string('email', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('avatar')->nullable();
            
            // Status
            $table->boolean('status')->default(1); // 1=active, 0=inactive
            
            // Specializations and working schedule
            $table->json('specializations')->nullable(); // Array of specialization strings
            $table->json('working_hours')->nullable(); // {"monday": {"open": "09:00", "close": "18:00"}, ...}
            $table->json('breaks')->nullable(); // Array of break periods
            $table->json('days_off')->nullable(); // Array of dates when staff is off
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('salon_id', 'idx_beauty_staff_salon_id');
            $table->index('status', 'idx_beauty_staff_status');
            $table->index(['salon_id', 'status'], 'idx_beauty_staff_salon_status');
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
        Schema::dropIfExists('beauty_staff');
    }
}

