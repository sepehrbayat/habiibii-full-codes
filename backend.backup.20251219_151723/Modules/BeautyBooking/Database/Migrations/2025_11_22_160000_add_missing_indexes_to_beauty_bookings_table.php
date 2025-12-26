<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add Missing Indexes to Beauty Bookings Table Migration
 * Migration برای اضافه کردن ایندکس‌های مفقود به جدول رزروها
 */
class AddMissingIndexesToBeautyBookingsTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::table('beauty_bookings', function (Blueprint $table) {
            // Index for booking_date_time (used in overlapping booking checks)
            // ایندکس برای booking_date_time (استفاده در بررسی رزروهای همپوشان)
            if (!$this->indexExists('beauty_bookings', 'idx_beauty_bookings_booking_date_time')) {
                $table->index('booking_date_time', 'idx_beauty_bookings_booking_date_time');
            }
            
            // Composite index for common query pattern (salon_id, staff_id, booking_date)
            // ایندکس ترکیبی برای الگوی کوئری رایج (salon_id, staff_id, booking_date)
            if (!$this->indexExists('beauty_bookings', 'idx_beauty_bookings_salon_staff_date')) {
                $table->index(['salon_id', 'staff_id', 'booking_date'], 'idx_beauty_bookings_salon_staff_date');
            }
            
            // Composite index for status and payment_status queries
            // ایندکس ترکیبی برای کوئری‌های status و payment_status
            if (!$this->indexExists('beauty_bookings', 'idx_beauty_bookings_status_payment')) {
                $table->index(['status', 'payment_status'], 'idx_beauty_bookings_status_payment');
            }
        });
    }

    /**
     * Reverse the migrations.
     * برگشت migration
     *
     * Note: Check for index existence before dropping to ensure migration rollback
     * works correctly even if indexes don't exist
     * توجه: بررسی وجود ایندکس قبل از حذف برای اطمینان از کارکرد صحیح rollback migration
     * حتی در صورت عدم وجود ایندکس‌ها
     *
     * @return void
     */
    public function down()
    {
        Schema::table('beauty_bookings', function (Blueprint $table) {
            // Drop indexes only if they exist to prevent rollback failures
            // حذف ایندکس‌ها فقط در صورت وجود برای جلوگیری از شکست rollback
            if ($this->indexExists('beauty_bookings', 'idx_beauty_bookings_booking_date_time')) {
                $table->dropIndex('idx_beauty_bookings_booking_date_time');
            }
            
            if ($this->indexExists('beauty_bookings', 'idx_beauty_bookings_salon_staff_date')) {
                $table->dropIndex('idx_beauty_bookings_salon_staff_date');
            }
            
            if ($this->indexExists('beauty_bookings', 'idx_beauty_bookings_status_payment')) {
                $table->dropIndex('idx_beauty_bookings_status_payment');
            }
        });
    }
    
    /**
     * Check if index exists (database-agnostic)
     * بررسی وجود ایندکس (مستقل از نوع دیتابیس)
     *
     * Uses Laravel's Schema::hasIndex() which is database-agnostic and works
     * with MySQL, PostgreSQL, SQLite, and other database drivers supported by Laravel
     * استفاده از Schema::hasIndex() لاراول که مستقل از نوع دیتابیس است و با
     * MySQL، PostgreSQL، SQLite و سایر درایورهای دیتابیس پشتیبانی شده توسط لاراول کار می‌کند
     *
     * @param string $table
     * @param string $indexName
     * @return bool
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            // Laravel 10+ provides Schema::hasIndex() which is database-agnostic
            // لاراول 10+ متد Schema::hasIndex() را ارائه می‌دهد که مستقل از نوع دیتابیس است
            return Schema::hasIndex($table, $indexName);
        } catch (\Exception $e) {
            // If table doesn't exist or any error occurs, assume index doesn't exist
            // در صورت عدم وجود جدول یا بروز هرگونه خطا، فرض می‌کنیم ایندکس وجود ندارد
            \Log::warning('Failed to check index existence', [
                'table' => $table,
                'index' => $indexName,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}

