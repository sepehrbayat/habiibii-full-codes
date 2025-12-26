<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add Cancellation Stats to Beauty Salons Table Migration
 * Migration برای افزودن آمار لغو به جدول سالن‌ها
 */
class AddCancellationStatsToBeautySalonsTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::table('beauty_salons', function (Blueprint $table) {
            // Total number of cancelled bookings
            // تعداد کل رزروهای لغو شده
            $table->integer('total_cancellations')
                ->default(0)
                ->after('total_reviews')
                ->comment('Total number of cancelled bookings');
            
            // Cancellation rate percentage (0-100)
            // درصد نرخ لغو (0-100)
            $table->decimal('cancellation_rate', 5, 2)
                ->default(0.00)
                ->after('total_cancellations')
                ->comment('Cancellation rate percentage (0-100)');
            
            // Index for filtering by cancellation rate
            $table->index('cancellation_rate', 'idx_beauty_salons_cancellation_rate');
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
        Schema::table('beauty_salons', function (Blueprint $table) {
            $table->dropIndex('idx_beauty_salons_cancellation_rate');
            $table->dropColumn(['total_cancellations', 'cancellation_rate']);
        });
    }
}

