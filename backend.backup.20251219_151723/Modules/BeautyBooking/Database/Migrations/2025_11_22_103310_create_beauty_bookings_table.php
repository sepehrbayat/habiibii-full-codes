<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Bookings Table Migration
 * Migration برای ایجاد جدول رزروها
 */
class CreateBeautyBookingsTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_bookings', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('salon_id')
                ->constrained('beauty_salons')
                ->onDelete('cascade');
            $table->foreignId('service_id')
                ->constrained('beauty_services')
                ->onDelete('cascade');
            $table->foreignId('staff_id')
                ->nullable()
                ->constrained('beauty_staff')
                ->onDelete('set null');
            $table->foreignId('zone_id')
                ->nullable()
                ->constrained('zones')
                ->onDelete('set null');
            
            // Booking details
            $table->date('booking_date');
            $table->time('booking_time');
            $table->dateTime('booking_date_time')->nullable(); // Combined datetime for easier queries
            $table->string('booking_reference', 50)->unique();
            
            // Status
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'])
                ->default('pending');
            $table->enum('payment_status', ['paid', 'unpaid', 'partially_paid'])
                ->default('unpaid');
            $table->string('payment_method', 50)->nullable();
            
            // Amounts
            $table->decimal('total_amount', 23, 8)->default(0.00);
            $table->decimal('commission_amount', 23, 8)->default(0.00);
            $table->decimal('service_fee', 23, 8)->default(0.00);
            $table->decimal('cancellation_fee', 23, 8)->default(0.00);
            
            // Additional information
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->enum('cancelled_by', ['customer', 'salon', 'admin', 'none'])->default('none');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('user_id', 'idx_beauty_bookings_user_id');
            $table->index('salon_id', 'idx_beauty_bookings_salon_id');
            $table->index('service_id', 'idx_beauty_bookings_service_id');
            $table->index('staff_id', 'idx_beauty_bookings_staff_id');
            $table->index('status', 'idx_beauty_bookings_status');
            $table->index('payment_status', 'idx_beauty_bookings_payment_status');
            $table->index('booking_date', 'idx_beauty_bookings_booking_date');
            $table->index('booking_reference', 'idx_beauty_bookings_booking_reference');
            $table->index(['salon_id', 'booking_date'], 'idx_beauty_bookings_salon_date');
            $table->index(['status', 'booking_date'], 'idx_beauty_bookings_status_date');
        });
        
        // Set auto increment starting point
        DB::statement('ALTER TABLE beauty_bookings AUTO_INCREMENT = 100000;');
    }

    /**
     * Reverse the migrations.
     * برگشت migration
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('beauty_bookings');
    }
}

