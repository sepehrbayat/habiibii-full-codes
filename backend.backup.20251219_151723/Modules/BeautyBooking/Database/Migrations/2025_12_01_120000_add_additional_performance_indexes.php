<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add Additional Performance Indexes Migration
 * Migration برای اضافه کردن ایندکس‌های عملکردی اضافی
 */
class AddAdditionalPerformanceIndexes extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        // Add indexes to beauty_bookings if they don't exist
        // افزودن ایندکس‌ها به beauty_bookings در صورت عدم وجود
        if (Schema::hasTable('beauty_bookings')) {
            Schema::table('beauty_bookings', function (Blueprint $table) {
                // Index for user bookings queries
                // ایندکس برای کوئری‌های رزروهای کاربر
                if (!$this->indexExists('beauty_bookings', 'idx_beauty_bookings_user_status')) {
                    $table->index(['user_id', 'status'], 'idx_beauty_bookings_user_status');
                }
            });
        }
        
        // Add indexes to beauty_reviews if they don't exist
        // افزودن ایندکس‌ها به beauty_reviews در صورت عدم وجود
        if (Schema::hasTable('beauty_reviews')) {
            Schema::table('beauty_reviews', function (Blueprint $table) {
                // Index already exists, but ensure composite index for rating queries
                // ایندکس قبلاً وجود دارد، اما اطمینان از ایندکس ترکیبی برای کوئری‌های امتیاز
                if (!$this->indexExists('beauty_reviews', 'idx_beauty_reviews_salon_rating')) {
                    $table->index(['salon_id', 'rating', 'status'], 'idx_beauty_reviews_salon_rating');
                }
            });
        }
        
        // Add indexes to beauty_packages if they don't exist
        // افزودن ایندکس‌ها به beauty_packages در صورت عدم وجود
        if (Schema::hasTable('beauty_packages')) {
            Schema::table('beauty_packages', function (Blueprint $table) {
                // Index already exists, no additional needed
                // ایندکس قبلاً وجود دارد، نیاز به اضافه نیست
            });
        }
        
        // Add indexes to beauty_subscriptions if they don't exist
        // افزودن ایندکس‌ها به beauty_subscriptions در صورت عدم وجود
        if (Schema::hasTable('beauty_subscriptions')) {
            Schema::table('beauty_subscriptions', function (Blueprint $table) {
                // Index for active subscriptions query
                // ایندکس برای کوئری اشتراک‌های فعال
                if (!$this->indexExists('beauty_subscriptions', 'idx_beauty_subscriptions_active')) {
                    $table->index(['status', 'end_date'], 'idx_beauty_subscriptions_active');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     * برگشت migration
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('beauty_bookings')) {
            Schema::table('beauty_bookings', function (Blueprint $table) {
                if ($this->indexExists('beauty_bookings', 'idx_beauty_bookings_user_status')) {
                    $table->dropIndex('idx_beauty_bookings_user_status');
                }
            });
        }
        
        if (Schema::hasTable('beauty_reviews')) {
            Schema::table('beauty_reviews', function (Blueprint $table) {
                if ($this->indexExists('beauty_reviews', 'idx_beauty_reviews_salon_rating')) {
                    $table->dropIndex('idx_beauty_reviews_salon_rating');
                }
            });
        }
        
        if (Schema::hasTable('beauty_subscriptions')) {
            Schema::table('beauty_subscriptions', function (Blueprint $table) {
                if ($this->indexExists('beauty_subscriptions', 'idx_beauty_subscriptions_active')) {
                    $table->dropIndex('idx_beauty_subscriptions_active');
                }
            });
        }
    }
    
    /**
     * Check if index exists
     * بررسی وجود ایندکس
     *
     * @param string $table
     * @param string $indexName
     * @return bool
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            return Schema::hasIndex($table, $indexName);
        } catch (\Exception $e) {
            \Log::warning('Failed to check index existence', [
                'table' => $table,
                'index' => $indexName,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}

