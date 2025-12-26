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
        if (!Schema::hasTable('discounts')) {
            Schema::create('discounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('set null');
                $table->foreignId('module_id')->nullable()->constrained('modules')->onDelete('set null');
                $table->string('discount_type', 50)->nullable();
                $table->decimal('discount', 23, 2)->default(0);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->index('store_id', 'idx_discounts_store_id');
                $table->index('module_id', 'idx_discounts_module_id');
                $table->index('status', 'idx_discounts_status');
                $table->index('start_date', 'idx_discounts_start_date');
                $table->index('end_date', 'idx_discounts_end_date');
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
        Schema::dropIfExists('discounts');
    }
};

