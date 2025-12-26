<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Package Usages Table Migration
 * Migration برای ایجاد جدول استفاده از پکیج‌ها
 */
class CreateBeautyPackageUsagesTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_package_usages', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('package_id')
                ->constrained('beauty_packages')
                ->onDelete('cascade');
            $table->foreignId('booking_id')
                ->nullable()
                ->constrained('beauty_bookings')
                ->onDelete('set null');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('salon_id')
                ->constrained('beauty_salons')
                ->onDelete('cascade');
            
            // Usage details
            $table->integer('session_number')->comment('Session number (1, 2, 3, etc.)');
            $table->dateTime('used_at')->comment('When this session was used');
            
            // Status
            $table->enum('status', ['pending', 'used', 'expired', 'cancelled'])
                ->default('pending');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('package_id', 'idx_beauty_package_usages_package_id');
            $table->index('user_id', 'idx_beauty_package_usages_user_id');
            $table->index('booking_id', 'idx_beauty_package_usages_booking_id');
            $table->index('status', 'idx_beauty_package_usages_status');
            $table->index(['package_id', 'user_id'], 'idx_beauty_package_usages_package_user');
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
        Schema::dropIfExists('beauty_package_usages');
    }
}

