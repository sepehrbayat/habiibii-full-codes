<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create delivery_men table if missing
 * ایجاد جدول پیک‌ها در صورت عدم وجود
 */
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
        if (Schema::hasTable('delivery_men')) {
            return;
        }

        Schema::create('delivery_men', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('f_name', 100)->nullable();
            $table->string('l_name', 100)->nullable();
            $table->string('phone', 20)->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('identity_number', 100)->nullable();
            $table->string('identity_type', 50)->nullable();
            $table->json('identity_image')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->enum('application_status', ['pending', 'approved', 'denied'])->default('pending');
            $table->enum('type', ['zone_wise', 'restaurant_wise'])->default('zone_wise');
            $table->tinyInteger('active')->default(1);
            $table->tinyInteger('available')->default(1);
            $table->boolean('status')->default(true);
            $table->tinyInteger('earning')->default(1);
            $table->integer('current_orders')->default(0);
            $table->string('auth_token', 255)->nullable();
            $table->string('fcm_token', 255)->nullable();
            $table->string('image', 255)->nullable();
            $table->string('login_remember_token', 255)->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('zone_id', 'idx_delivery_men_zone_id');
            $table->index('store_id', 'idx_delivery_men_store_id');
            $table->index('vehicle_id', 'idx_delivery_men_vehicle_id');
            $table->index('application_status', 'idx_delivery_men_app_status');
            $table->index('type', 'idx_delivery_men_type');
        });
    }

    /**
     * Reverse the migrations.
     * بازگردانی تغییرات
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasTable('delivery_men')) {
            Schema::dropIfExists('delivery_men');
        }
    }
};

