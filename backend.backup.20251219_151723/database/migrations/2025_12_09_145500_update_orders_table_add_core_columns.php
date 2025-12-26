<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add missing core columns to orders table for dashboard queries
 * اضافه کردن ستون‌های اصلی برای جدول سفارش جهت گزارش‌های داشبورد
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * اجرای مایگریشن
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (!Schema::hasColumn('orders', 'delivery_man_id')) {
                $table->unsignedBigInteger('delivery_man_id')->nullable()->after('store_id');
                $table->index('delivery_man_id', 'idx_orders_delivery_man_id');
            }

            if (!Schema::hasColumn('orders', 'module_id')) {
                $table->unsignedBigInteger('module_id')->nullable()->after('store_id');
                $table->index('module_id', 'idx_orders_module_id');
            }

            if (!Schema::hasColumn('orders', 'zone_id')) {
                $table->unsignedBigInteger('zone_id')->nullable()->after('module_id');
                $table->index('zone_id', 'idx_orders_zone_id');
            }

            if (!Schema::hasColumn('orders', 'schedule_at')) {
                $table->timestamp('schedule_at')->nullable()->after('created_at');
                $table->index('schedule_at', 'idx_orders_schedule_at');
            }

            if (!Schema::hasColumn('orders', 'scheduled')) {
                $table->tinyInteger('scheduled')->default(0)->after('schedule_at');
            }

            if (!Schema::hasColumn('orders', 'accepted')) {
                $table->timestamp('accepted')->nullable()->after('scheduled');
            }

            if (!Schema::hasColumn('orders', 'processing')) {
                $table->timestamp('processing')->nullable()->after('accepted');
            }

            if (!Schema::hasColumn('orders', 'picked_up')) {
                $table->timestamp('picked_up')->nullable()->after('processing');
            }

            if (!Schema::hasColumn('orders', 'delivered')) {
                $table->timestamp('delivered')->nullable()->after('picked_up');
            }

            if (!Schema::hasColumn('orders', 'canceled')) {
                $table->timestamp('canceled')->nullable()->after('delivered');
            }

            if (!Schema::hasColumn('orders', 'refund_requested')) {
                $table->timestamp('refund_requested')->nullable()->after('canceled');
            }

            if (!Schema::hasColumn('orders', 'refunded')) {
                $table->timestamp('refunded')->nullable()->after('refund_requested');
            }
        });
    }

    /**
     * Reverse the migrations.
     * بازگردانی تغییرات
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            foreach ([
                'idx_orders_delivery_man_id',
                'idx_orders_module_id',
                'idx_orders_zone_id',
                'idx_orders_schedule_at',
            ] as $index) {
                try {
                    $table->dropIndex($index);
                } catch (\Exception $exception) {
                    // Index might already be removed
                }
            }

            foreach ([
                'refunded',
                'refund_requested',
                'canceled',
                'delivered',
                'picked_up',
                'processing',
                'accepted',
                'scheduled',
                'schedule_at',
                'zone_id',
                'module_id',
                'delivery_man_id',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

