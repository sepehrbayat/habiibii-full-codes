<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * اجرای مایگریشن
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('store_schedule')) {
            Schema::create('store_schedule', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
                $table->tinyInteger('day')->default(0);
                $table->time('opening_time')->nullable();
                $table->time('closing_time')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->index('store_id', 'idx_store_schedule_store_id');
                $table->index('day', 'idx_store_schedule_day');
                $table->index('status', 'idx_store_schedule_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     * برگشت مایگریشن
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('store_schedule');
    }
};

