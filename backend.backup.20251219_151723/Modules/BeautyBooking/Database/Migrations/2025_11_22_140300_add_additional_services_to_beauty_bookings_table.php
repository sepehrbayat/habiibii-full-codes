<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add Additional Services to Beauty Bookings Table Migration
 * Migration برای افزودن خدمات اضافی به جدول رزروها
 */
class AddAdditionalServicesToBeautyBookingsTable extends Migration
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
            // Additional services (cross-sell/upsell) booked with main service
            // خدمات اضافی (فروش متقابل/افزایش فروش) رزرو شده با خدمت اصلی
            // JSON array: [{"service_id": 1, "price": 50000, "name": "Service Name"}, ...]
            $table->json('additional_services')
                ->nullable()
                ->after('consultation_credit_amount')
                ->comment('Array of additional cross-sell/upsell services booked with this booking');
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
            $table->dropColumn('additional_services');
        });
    }
}

