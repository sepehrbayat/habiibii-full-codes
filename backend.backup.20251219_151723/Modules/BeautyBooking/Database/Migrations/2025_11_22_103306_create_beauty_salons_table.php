<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Salons Table Migration
 * Migration برای ایجاد جدول سالن‌های زیبایی
 */
class CreateBeautySalonsTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_salons', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('store_id')
                ->constrained('stores')
                ->onDelete('cascade');
            $table->foreignId('zone_id')
                ->nullable()
                ->constrained('zones')
                ->onDelete('set null');
            
            // Business type and license
            $table->enum('business_type', ['salon', 'clinic'])->default('salon');
            $table->string('license_number', 100)->nullable();
            $table->date('license_expiry')->nullable();
            $table->json('documents')->nullable(); // Array of document file paths
            
            // Status and verification
            $table->tinyInteger('verification_status')->default(0); // 0=pending, 1=approved, 2=rejected
            $table->text('verification_notes')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            
            // Business hours and calendar
            $table->json('working_hours')->nullable(); // {"monday": {"open": "09:00", "close": "18:00"}, ...}
            $table->json('holidays')->nullable(); // Array of holiday dates
            
            // Statistics
            $table->decimal('avg_rating', 3, 2)->default(0.00);
            $table->integer('total_bookings')->default(0);
            $table->integer('total_reviews')->default(0);
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('verification_status', 'idx_beauty_salons_verification_status');
            $table->index('is_verified', 'idx_beauty_salons_is_verified');
            $table->index('is_featured', 'idx_beauty_salons_is_featured');
            $table->index('avg_rating', 'idx_beauty_salons_avg_rating');
            $table->index(['store_id', 'verification_status'], 'idx_beauty_salons_store_verification');
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
        Schema::dropIfExists('beauty_salons');
    }
}

