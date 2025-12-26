<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Calendar Blocks Table Migration
 * Migration برای ایجاد جدول بلاک‌های تقویم
 */
class CreateBeautyCalendarBlocksTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_calendar_blocks', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('salon_id')
                ->constrained('beauty_salons')
                ->onDelete('cascade');
            $table->foreignId('staff_id')
                ->nullable()
                ->constrained('beauty_staff')
                ->onDelete('cascade');
            
            // Block details
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('type', ['break', 'holiday', 'manual_block'])->default('manual_block');
            $table->text('reason')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('salon_id', 'idx_beauty_calendar_blocks_salon_id');
            $table->index('staff_id', 'idx_beauty_calendar_blocks_staff_id');
            $table->index('date', 'idx_beauty_calendar_blocks_date');
            $table->index('type', 'idx_beauty_calendar_blocks_type');
            $table->index(['salon_id', 'date'], 'idx_beauty_calendar_blocks_salon_date');
            $table->index(['staff_id', 'date'], 'idx_beauty_calendar_blocks_staff_date');
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
        Schema::dropIfExists('beauty_calendar_blocks');
    }
}

