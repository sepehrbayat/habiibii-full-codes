<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add refund status values to payment_status enum
 * افزودن مقادیر وضعیت بازگشت وجه به enum payment_status
 */
class AddRefundStatusToBeautyBookingsPaymentStatus extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        // Expand enum to include all refund-related statuses in a single step
        // گسترش enum برای افزودن تمام وضعیت‌های مرتبط با بازگشت وجه در یک مرحله
        // Note: MySQL doesn't support ALTER ENUM directly, so we need to use MODIFY COLUMN
        // توجه: MySQL از ALTER ENUM به طور مستقیم پشتیبانی نمی‌کند، بنابراین باید از MODIFY COLUMN استفاده کنیم
        DB::statement("ALTER TABLE beauty_bookings MODIFY COLUMN payment_status ENUM('paid', 'unpaid', 'partially_paid', 'refunded', 'refund_pending', 'refund_failed') DEFAULT 'unpaid'");
    }

    /**
     * Reverse the migrations.
     * برگشت migration
     *
     * @return void
     */
    public function down()
    {
        // Revert to original enum from table creation
        // بازگردانی به enum اولیه در زمان ایجاد جدول
        DB::statement("ALTER TABLE beauty_bookings MODIFY COLUMN payment_status ENUM('paid', 'unpaid', 'partially_paid') DEFAULT 'unpaid'");
    }
}

