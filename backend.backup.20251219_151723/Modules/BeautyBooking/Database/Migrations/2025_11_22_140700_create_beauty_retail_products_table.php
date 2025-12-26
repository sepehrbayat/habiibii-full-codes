<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Retail Products Table Migration
 * Migration برای ایجاد جدول محصولات خرده‌فروشی
 */
class CreateBeautyRetailProductsTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_retail_products', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('salon_id')
                ->constrained('beauty_salons')
                ->onDelete('cascade');
            
            // Product information
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('price', 23, 8)->default(0.00);
            $table->string('image')->nullable();
            $table->string('category', 100)->nullable()->comment('Product category (e.g., skincare, makeup, tools)');
            
            // Inventory
            $table->integer('stock_quantity')->default(0)->comment('Available stock quantity');
            $table->integer('min_stock_level')->default(0)->comment('Minimum stock level for alerts');
            
            // Status
            $table->boolean('status')->default(1)->comment('Product active status');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('salon_id', 'idx_beauty_retail_products_salon_id');
            $table->index('category', 'idx_beauty_retail_products_category');
            $table->index('status', 'idx_beauty_retail_products_status');
            $table->index(['salon_id', 'status'], 'idx_beauty_retail_products_salon_status');
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
        Schema::dropIfExists('beauty_retail_products');
    }
}

