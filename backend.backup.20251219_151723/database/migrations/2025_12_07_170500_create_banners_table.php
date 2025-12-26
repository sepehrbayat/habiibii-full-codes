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
        if (!Schema::hasTable('banners')) {
            Schema::create('banners', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->string('image')->nullable();
                $table->text('data')->nullable();
                $table->string('type')->nullable();
                $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
                $table->foreignId('module_id')->nullable()->constrained('modules')->onDelete('set null');
                $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('set null');
                $table->foreignId('item_id')->nullable()->constrained('items')->onDelete('set null');
                $table->tinyInteger('status')->default(1);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->timestamps();

                $table->index('zone_id', 'idx_banners_zone_id');
                $table->index('module_id', 'idx_banners_module_id');
                $table->index('store_id', 'idx_banners_store_id');
                $table->index('item_id', 'idx_banners_item_id');
                $table->index('status', 'idx_banners_status');
                $table->index('start_date', 'idx_banners_start_date');
                $table->index('end_date', 'idx_banners_end_date');
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
        Schema::dropIfExists('banners');
    }
};

