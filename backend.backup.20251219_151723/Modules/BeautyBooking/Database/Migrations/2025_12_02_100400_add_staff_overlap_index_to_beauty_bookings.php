<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add Staff-Specific Overlap Check Index to Beauty Bookings Table
 * افزودن ایندکس بررسی همپوشانی مخصوص کارمند به جدول رزروها
 *
 * Optimizes the booking overlap check query when staff_id is specified
 * بهینه‌سازی کوئری بررسی همپوشانی رزرو زمانی که staff_id مشخص شده است
 */
class AddStaffOverlapIndexToBeautyBookings extends Migration
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
            // Composite index optimized for staff-specific overlap queries
            // ایندکس ترکیبی بهینه شده برای کوئری‌های همپوشانی مخصوص کارمند
            // This index helps when filtering by staff_id in overlap checks
            // این ایندکس هنگام فیلتر بر اساس staff_id در بررسی‌های همپوشانی کمک می‌کند
            // Order: salon_id, staff_id, booking_date, status, booking_date_time
            // ترتیب: salon_id، staff_id، booking_date، status، booking_date_time
            if (!$this->indexExists('beauty_bookings', 'idx_beauty_bookings_staff_overlap')) {
                $table->index(
                    ['salon_id', 'staff_id', 'booking_date', 'status', 'booking_date_time'],
                    'idx_beauty_bookings_staff_overlap'
                );
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
            if ($this->indexExists('beauty_bookings', 'idx_beauty_bookings_staff_overlap')) {
                $table->dropIndex('idx_beauty_bookings_staff_overlap');
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

