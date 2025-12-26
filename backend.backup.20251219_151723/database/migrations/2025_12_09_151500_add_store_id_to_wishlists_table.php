<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add store_id to wishlists for store-level popularity queries
 * افزودن store_id به جدول علاقه‌مندی‌ها برای کوئری‌های محبوبیت فروشگاه
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
        if (!Schema::hasColumn('wishlists', 'store_id')) {
            Schema::table('wishlists', function (Blueprint $table): void {
                $table->unsignedBigInteger('store_id')->nullable()->after('user_id');
                $table->index('store_id', 'idx_wishlists_store_id');
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
        Schema::table('wishlists', function (Blueprint $table): void {
            if (Schema::hasColumn('wishlists', 'store_id')) {
                try {
                    $table->dropIndex('idx_wishlists_store_id');
                } catch (\Exception $exception) {
                    // index might already be dropped
                }
                $table->dropColumn('store_id');
            }
        });
    }
};

