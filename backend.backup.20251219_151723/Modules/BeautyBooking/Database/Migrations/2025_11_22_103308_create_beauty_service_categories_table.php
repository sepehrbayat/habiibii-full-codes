<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Service Categories Table Migration
 * Migration برای ایجاد جدول دسته‌بندی خدمات
 */
class CreateBeautyServiceCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_service_categories', function (Blueprint $table) {
            $table->id();
            
            // Self-referential for subcategories
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('beauty_service_categories')
                ->onDelete('cascade');
            
            // Category information
            $table->string('name', 255);
            $table->string('image')->nullable();
            $table->boolean('status')->default(1);
            $table->integer('sort_order')->default(0);
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('parent_id', 'idx_beauty_service_categories_parent_id');
            $table->index('status', 'idx_beauty_service_categories_status');
            $table->index('sort_order', 'idx_beauty_service_categories_sort_order');
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
        Schema::dropIfExists('beauty_service_categories');
    }
}

