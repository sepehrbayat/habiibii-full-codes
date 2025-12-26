<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add rating_count column needed for dashboard top-rated items queries
 * افزودن ستون تعداد امتیاز برای گزارش آیتم‌های برتر داشبورد
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
        if (!Schema::hasColumn('items', 'rating_count')) {
            Schema::table('items', function (Blueprint $table): void {
                $table->integer('rating_count')->default(0)->after('order_count');
                $table->index('rating_count', 'idx_items_rating_count');
            });
        }
    }

    /**
     * Reverse the migrations.
     * بازگردانی تغییرات
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table): void {
            if (Schema::hasColumn('items', 'rating_count')) {
                try {
                    $table->dropIndex('idx_items_rating_count');
                } catch (\Exception $exception) {
                    // index may already be dropped
                }
                $table->dropColumn('rating_count');
            }
        });
    }
};

