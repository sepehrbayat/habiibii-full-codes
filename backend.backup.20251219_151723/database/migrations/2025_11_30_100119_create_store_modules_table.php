<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create store_modules pivot table
 * ایجاد جدول pivot برای دسترسی ماژول‌های فروشگاه
 * 
 * This table allows stores to access multiple modules beyond their primary module_id
 * این جدول به فروشگاه‌ها اجازه می‌دهد به چندین ماژول علاوه بر module_id اصلی دسترسی داشته باشند
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('store_modules', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('store_id')
                ->constrained('stores')
                ->onDelete('cascade');
            $table->foreignId('module_id')
                ->constrained('modules')
                ->onDelete('cascade');
            
            // Status flag to enable/disable module access
            $table->boolean('status')->default(true);
            
            // Timestamps
            $table->timestamps();
            
            // Unique constraint to prevent duplicate entries
            $table->unique(['store_id', 'module_id'], 'unique_store_module');
            
            // Indexes for performance
            $table->index('store_id', 'idx_store_modules_store_id');
            $table->index('module_id', 'idx_store_modules_module_id');
            $table->index('status', 'idx_store_modules_status');
        });
    }

    /**
     * Reverse the migrations.
     * برگشت migration
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('store_modules');
    }
};
