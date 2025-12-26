<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Services Table Migration
 * Migration برای ایجاد جدول خدمات
 */
class CreateBeautyServicesTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_services', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('salon_id')
                ->constrained('beauty_salons')
                ->onDelete('cascade');
            $table->foreignId('category_id')
                ->constrained('beauty_service_categories')
                ->onDelete('cascade');
            
            // Service information
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->integer('duration_minutes'); // Service duration in minutes
            $table->decimal('price', 23, 8)->default(0.00);
            $table->string('image')->nullable();
            $table->boolean('status')->default(1);
            
            // Staff assignment (which staff members can perform this service)
            $table->json('staff_ids')->nullable(); // Array of staff IDs
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('salon_id', 'idx_beauty_services_salon_id');
            $table->index('category_id', 'idx_beauty_services_category_id');
            $table->index('status', 'idx_beauty_services_status');
            $table->index(['salon_id', 'status'], 'idx_beauty_services_salon_status');
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
        Schema::dropIfExists('beauty_services');
    }
}

