<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add Consultation Fields to Beauty Bookings Table Migration
 * Migration برای افزودن فیلدهای مشاوره به جدول رزروها
 */
class AddConsultationFieldsToBeautyBookingsTable extends Migration
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
            // Main service ID if this booking is a consultation that can be credited to a main service
            // شناسه خدمت اصلی در صورت اینکه این رزرو یک مشاوره باشد که می‌تواند به خدمت اصلی اعتبار شود
            $table->foreignId('main_service_id')
                ->nullable()
                ->after('service_id')
                ->constrained('beauty_services')
                ->onDelete('set null');
            
            // Consultation credit percentage applied to main service
            // درصد اعتبار مشاوره اعمال شده به خدمت اصلی
            $table->decimal('consultation_credit_percentage', 5, 2)
                ->default(0.00)
                ->after('main_service_id')
                ->comment('Percentage (0-100) of consultation fee credited to main service');
            
            // Consultation credit amount applied to main service booking
            // مبلغ اعتبار مشاوره اعمال شده به رزرو خدمت اصلی
            $table->decimal('consultation_credit_amount', 23, 8)
                ->default(0.00)
                ->after('consultation_credit_percentage')
                ->comment('Amount of consultation fee credited to main service booking');
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
            $table->dropForeign(['main_service_id']);
            $table->dropColumn(['main_service_id', 'consultation_credit_percentage', 'consultation_credit_amount']);
        });
    }
}

