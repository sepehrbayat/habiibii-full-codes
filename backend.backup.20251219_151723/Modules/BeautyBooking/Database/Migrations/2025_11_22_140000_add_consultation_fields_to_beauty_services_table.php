<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add Consultation Fields to Beauty Services Table Migration
 * Migration برای افزودن فیلدهای مشاوره به جدول خدمات
 */
class AddConsultationFieldsToBeautyServicesTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::table('beauty_services', function (Blueprint $table) {
            // Service type: 'service', 'pre_consultation', 'post_consultation'
            // نوع خدمت: 'service', 'pre_consultation', 'post_consultation'
            $table->enum('service_type', ['service', 'pre_consultation', 'post_consultation'])
                ->default('service')
                ->after('status');
            
            // Percentage of consultation fee that can be credited to main service
            // درصدی از هزینه مشاوره که می‌تواند به خدمت اصلی اعتبار شود
            $table->decimal('consultation_credit_percentage', 5, 2)
                ->default(0.00)
                ->after('service_type')
                ->comment('Percentage (0-100) of consultation fee that can be credited to main service booking');
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
        Schema::table('beauty_services', function (Blueprint $table) {
            $table->dropColumn(['service_type', 'consultation_credit_percentage']);
        });
    }
}

