<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add Unique Constraints to Beauty Transactions Table Migration
 * Migration برای افزودن محدودیت‌های یکتا به جدول تراکنش‌های زیبایی
 *
 * Prevents duplicate transaction entries at database level
 * جلوگیری از ورودی‌های تراکنش تکراری در سطح دیتابیس
 */
class AddUniqueConstraintToBeautyTransactions extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::table('beauty_transactions', function (Blueprint $table) {
            // Unique constraint for booking-related transactions
            // محدودیت یکتا برای تراکنش‌های مربوط به رزرو
            // Note: MySQL treats NULL values as distinct, so multiple NULL booking_ids are allowed
            // توجه: MySQL مقادیر NULL را متمایز می‌داند، بنابراین چندین booking_id NULL مجاز است
            // This is intentional - non-booking transactions (subscriptions, ads) use reference_number instead
            // این عمدی است - تراکنش‌های غیر رزرو (اشتراک‌ها، تبلیغات) به جای آن از reference_number استفاده می‌کنند
            if (!$this->indexExists('beauty_transactions', 'uq_beauty_transactions_booking_type')) {
                $table->unique(['booking_id', 'transaction_type'], 'uq_beauty_transactions_booking_type');
            }
            
            // Unique constraint for non-booking transactions using reference_number
            // محدودیت یکتا برای تراکنش‌های غیر رزرو با استفاده از reference_number
            // Note: This allows multiple NULL reference_numbers (for transactions without reference)
            // توجه: این اجازه می‌دهد چندین reference_number NULL (برای تراکنش‌های بدون مرجع)
            // But prevents duplicates when reference_number is set (e.g., subscriptions)
            // اما از تکرار جلوگیری می‌کند زمانی که reference_number تنظیم شده است (مثلاً اشتراک‌ها)
            if (!$this->indexExists('beauty_transactions', 'uq_beauty_transactions_ref_type')) {
                $table->unique(['reference_number', 'transaction_type'], 'uq_beauty_transactions_ref_type');
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
        Schema::table('beauty_transactions', function (Blueprint $table) {
            // Drop unique constraints only if they exist
            // حذف محدودیت‌های یکتا فقط در صورت وجود
            if ($this->indexExists('beauty_transactions', 'uq_beauty_transactions_booking_type')) {
                $table->dropUnique('uq_beauty_transactions_booking_type');
            }
            
            if ($this->indexExists('beauty_transactions', 'uq_beauty_transactions_ref_type')) {
                $table->dropUnique('uq_beauty_transactions_ref_type');
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

