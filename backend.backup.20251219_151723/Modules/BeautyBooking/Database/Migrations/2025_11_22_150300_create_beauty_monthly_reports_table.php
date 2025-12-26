<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Monthly Reports Table Migration
 * Migration برای ایجاد جدول گزارش‌های ماهانه
 */
class CreateBeautyMonthlyReportsTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_monthly_reports', function (Blueprint $table) {
            $table->id();
            
            // Report period
            // دوره گزارش
            $table->year('year');
            $table->tinyInteger('month'); // 1-12
            
            // Report type
            // نوع گزارش
            $table->enum('report_type', ['top_rated_salons', 'trending_clinics'])->default('top_rated_salons');
            
            // Report data (JSON array of salon IDs with their rankings)
            // داده گزارش (آرایه JSON از شناسه‌های سالن با رتبه‌بندی آنها)
            $table->json('salon_ids'); // Array of salon IDs in ranking order
            $table->json('salon_data')->nullable(); // Detailed data for each salon
            
            // Statistics
            // آمار
            $table->integer('total_salons')->default(0);
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['year', 'month', 'report_type'], 'idx_beauty_monthly_reports_period_type');
            $table->unique(['year', 'month', 'report_type'], 'unique_beauty_monthly_reports_period_type');
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
        Schema::dropIfExists('beauty_monthly_reports');
    }
}

