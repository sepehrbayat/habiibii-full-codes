<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add Overlap Check Index to Beauty Bookings Table Migration
 * Migration برای افزودن ایندکس بررسی همپوشانی به جدول رزروها
 *
 * Optimizes the booking overlap check query used in createBooking()
 * بهینه‌سازی کوئری بررسی همپوشانی رزرو استفاده شده در createBooking()
 */
class AddOverlapCheckIndexToBeautyBookings extends Migration
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
            // Composite index optimized for overlap lock query pattern
            // ایندکس ترکیبی بهینه شده برای الگوی کوئری قفل همپوشانی
            // The overlap query filters by: salon_id, booking_date, status != 'cancelled', and compares booking_date_time
            // کوئری همپوشانی فیلتر می‌کند بر اساس: salon_id، booking_date، status != 'cancelled'، و مقایسه booking_date_time
            // This index covers the exact query pattern for efficient lookups
            // این ایندکس الگوی کوئری دقیق را برای جستجوهای کارآمد پوشش می‌دهد
            if (!$this->indexExists('beauty_bookings', 'idx_beauty_bookings_overlap_check')) {
                $table->index(['salon_id', 'booking_date', 'status', 'booking_date_time'], 'idx_beauty_bookings_overlap_check');
            }
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
        Schema::table('beauty_bookings', function (Blueprint $table) {
            // Drop index only if it exists to prevent rollback failures
            // حذف ایندکس فقط در صورت وجود برای جلوگیری از شکست rollback
            if ($this->indexExists('beauty_bookings', 'idx_beauty_bookings_overlap_check')) {
                $table->dropIndex('idx_beauty_bookings_overlap_check');
            }
        });
    }
    
    /**
     * Check if index exists (database-agnostic)
     * بررسی وجود ایندکس (مستقل از نوع دیتابیس)
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

