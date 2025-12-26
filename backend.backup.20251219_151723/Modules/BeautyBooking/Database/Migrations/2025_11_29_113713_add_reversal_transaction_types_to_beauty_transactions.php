<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add Reversal Transaction Types to Beauty Transactions Migration
 * Migration برای افزودن انواع تراکنش برگردانی به تراکنش‌های زیبایی
 */
class AddReversalTransactionTypesToBeautyTransactions extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        // Modify enum to include reversal transaction types
        // تغییر enum برای شامل کردن انواع تراکنش برگردانی
        // Reversal types are created when bookings are cancelled to offset previously recorded revenue
        // انواع برگردانی زمانی ایجاد می‌شوند که رزروها لغو می‌شوند تا درآمد ثبت شده قبلی را جبران کنند
        // Includes cancellation_fee_reversal to handle reversal of cancellation fees when needed
        // شامل cancellation_fee_reversal برای مدیریت برگرداندن جریمه‌های لغو در صورت نیاز
        // Note: MySQL doesn't support ALTER ENUM directly, so we need to use MODIFY COLUMN
        // توجه: MySQL از ALTER ENUM به طور مستقیم پشتیبانی نمی‌کند، بنابراین باید از MODIFY COLUMN استفاده کنیم
        DB::statement("ALTER TABLE beauty_transactions MODIFY COLUMN transaction_type ENUM(
            'commission',
            'subscription',
            'advertisement',
            'service_fee',
            'package_sale',
            'cancellation_fee',
            'consultation_fee',
            'cross_selling',
            'retail_sale',
            'gift_card_sale',
            'loyalty_campaign',
            'commission_reversal',
            'service_fee_reversal',
            'package_sale_reversal',
            'consultation_fee_reversal',
            'cross_selling_reversal',
            'cancellation_fee_reversal'
        ) DEFAULT 'commission'");
    }

    /**
     * Reverse the migrations.
     * برگشت migration
     *
     * @return void
     */
    public function down()
    {
        // Remove reversal transaction types from enum
        // حذف انواع تراکنش برگردانی از enum
        DB::statement("ALTER TABLE beauty_transactions MODIFY COLUMN transaction_type ENUM(
            'commission',
            'subscription',
            'advertisement',
            'service_fee',
            'package_sale',
            'cancellation_fee',
            'consultation_fee',
            'cross_selling',
            'retail_sale',
            'gift_card_sale',
            'loyalty_campaign'
        ) DEFAULT 'commission'");
    }
}
