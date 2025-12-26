<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Retail Orders Table Migration
 * Migration برای ایجاد جدول سفارشات خرده‌فروشی
 */
class CreateBeautyRetailOrdersTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_retail_orders', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('salon_id')
                ->constrained('beauty_salons')
                ->onDelete('cascade');
            $table->foreignId('zone_id')
                ->nullable()
                ->constrained('zones')
                ->onDelete('set null');
            
            // Order details
            $table->string('order_reference', 50)->unique();
            $table->json('products')->comment('Array of products: [{"product_id": 1, "quantity": 2, "price": 50000}, ...]');
            
            // Amounts
            $table->decimal('subtotal', 23, 8)->default(0.00);
            $table->decimal('tax_amount', 23, 8)->default(0.00);
            $table->decimal('shipping_fee', 23, 8)->default(0.00);
            $table->decimal('discount', 23, 8)->default(0.00);
            $table->decimal('total_amount', 23, 8)->default(0.00);
            $table->decimal('commission_amount', 23, 8)->default(0.00);
            
            // Status
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])
                ->default('pending');
            $table->enum('payment_status', ['paid', 'unpaid', 'partially_paid'])
                ->default('unpaid');
            $table->string('payment_method', 50)->nullable();
            
            // Shipping information
            $table->text('shipping_address')->nullable();
            $table->string('shipping_phone', 20)->nullable();
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('user_id', 'idx_beauty_retail_orders_user_id');
            $table->index('salon_id', 'idx_beauty_retail_orders_salon_id');
            $table->index('status', 'idx_beauty_retail_orders_status');
            $table->index('payment_status', 'idx_beauty_retail_orders_payment_status');
            $table->index('order_reference', 'idx_beauty_retail_orders_order_reference');
            $table->index(['salon_id', 'status'], 'idx_beauty_retail_orders_salon_status');
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
        Schema::dropIfExists('beauty_retail_orders');
    }
}

