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
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code', 100)->unique();
                $table->foreignId('module_id')->nullable()->constrained('modules')->onDelete('set null');
                $table->tinyInteger('status')->default(1);
                $table->date('start_date')->nullable();
                $table->date('expire_date')->nullable();
                $table->timestamps();

                $table->index('module_id', 'idx_coupons_module_id');
                $table->index('status', 'idx_coupons_status');
                $table->index('start_date', 'idx_coupons_start_date');
                $table->index('expire_date', 'idx_coupons_expire_date');
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
        Schema::dropIfExists('coupons');
    }
};

