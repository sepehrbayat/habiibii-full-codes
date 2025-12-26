<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Packages Table Migration
 * Migration برای ایجاد جدول پکیج‌های چندجلسه‌ای
 */
class CreateBeautyPackagesTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_packages', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('salon_id')
                ->constrained('beauty_salons')
                ->onDelete('cascade');
            $table->foreignId('service_id')
                ->constrained('beauty_services')
                ->onDelete('cascade');
            
            // Package details
            $table->string('name', 255);
            $table->integer('sessions_count'); // Number of sessions in package
            $table->decimal('total_price', 23, 8)->default(0.00);
            $table->decimal('discount_percentage', 5, 2)->default(0.00); // Discount percentage
            $table->integer('validity_days'); // Package validity in days
            $table->boolean('status')->default(1);
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('salon_id', 'idx_beauty_packages_salon_id');
            $table->index('service_id', 'idx_beauty_packages_service_id');
            $table->index('status', 'idx_beauty_packages_status');
            $table->index(['salon_id', 'status'], 'idx_beauty_packages_salon_status');
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
        Schema::dropIfExists('beauty_packages');
    }
}

