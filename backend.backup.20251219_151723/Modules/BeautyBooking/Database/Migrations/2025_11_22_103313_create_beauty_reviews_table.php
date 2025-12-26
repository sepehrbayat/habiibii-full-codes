<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Reviews Table Migration
 * Migration برای ایجاد جدول نظرات
 */
class CreateBeautyReviewsTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_reviews', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('booking_id')
                ->constrained('beauty_bookings')
                ->onDelete('cascade');
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
            
            // Review content
            $table->tinyInteger('rating')->unsigned(); // 1-5
            $table->text('comment')->nullable();
            $table->json('attachments')->nullable(); // Array of image file paths
            
            // Moderation
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('booking_id', 'idx_beauty_reviews_booking_id');
            $table->index('user_id', 'idx_beauty_reviews_user_id');
            $table->index('salon_id', 'idx_beauty_reviews_salon_id');
            $table->index('service_id', 'idx_beauty_reviews_service_id');
            $table->index('staff_id', 'idx_beauty_reviews_staff_id');
            $table->index('status', 'idx_beauty_reviews_status');
            $table->index('rating', 'idx_beauty_reviews_rating');
            $table->index(['salon_id', 'status'], 'idx_beauty_reviews_salon_status');
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
        Schema::dropIfExists('beauty_reviews');
    }
}

