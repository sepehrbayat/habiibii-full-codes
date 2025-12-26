<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'store_id')) {
                    $table->unsignedBigInteger('store_id')->nullable()->after('user_id');
                    $table->index('store_id', 'idx_orders_store_id');
                    if (Schema::hasTable('stores')) {
                        $table->foreign('store_id')->references('id')->on('stores')->onDelete('set null');
                    }
                }
                if (!Schema::hasColumn('orders', 'order_status')) {
                    $table->string('order_status', 50)->default('pending')->after('store_id');
                    $table->index('order_status', 'idx_orders_order_status');
                }
                if (!Schema::hasColumn('orders', 'order_type')) {
                    $table->string('order_type', 50)->default('delivery')->after('order_status');
                    $table->index('order_type', 'idx_orders_order_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'order_type')) {
                    $table->dropIndex('idx_orders_order_type');
                    $table->dropColumn('order_type');
                }
                if (Schema::hasColumn('orders', 'order_status')) {
                    $table->dropIndex('idx_orders_order_status');
                    $table->dropColumn('order_status');
                }
                if (Schema::hasColumn('orders', 'store_id')) {
                    if (Schema::hasTable('stores')) {
                        $table->dropForeign(['store_id']);
                    }
                    $table->dropIndex('idx_orders_store_id');
                    $table->dropColumn('store_id');
                }
            });
        }
    }
};

