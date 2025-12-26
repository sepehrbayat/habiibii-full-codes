<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add Unique Constraint to Beauty Loyalty Points Table Migration
 * Migration برای افزودن محدودیت منحصر به فرد به جدول امتیازهای وفاداری
 *
 * Prevents duplicate point awards for the same booking and campaign combination
 * جلوگیری از اعطای تکراری امتیاز برای همان ترکیب رزرو و کمپین
 */
class AddUniqueConstraintToBeautyLoyaltyPoints extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::table('beauty_loyalty_points', function (Blueprint $table) {
            // Add normalized column without generated expression (MariaDB/MySQL restriction)
            // افزودن ستون نرمال‌شده بدون عبارت تولیدی (محدودیت MariaDB/MySQL)
            $table->unsignedBigInteger('booking_id_normalized')
                ->default(0)
                ->after('booking_id');
        });

        // Backfill normalized values safely
        // پر کردن مقادیر نرمال‌شده به‌صورت ایمن
        DB::table('beauty_loyalty_points')->update([
            'booking_id_normalized' => DB::raw('COALESCE(booking_id, 0)'),
        ]);

        Schema::table('beauty_loyalty_points', function (Blueprint $table) {
            // Enforce uniqueness using normalized column
            // اعمال یکتا بودن با استفاده از ستون نرمال‌شده
            $table->unique(
                ['booking_id_normalized', 'campaign_id', 'type'],
                'uq_beauty_loyalty_points_booking_campaign_type'
            );
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
        Schema::table('beauty_loyalty_points', function (Blueprint $table) {
            $table->dropUnique('uq_beauty_loyalty_points_booking_campaign_type');
            $table->dropColumn('booking_id_normalized');
        });
    }
}

