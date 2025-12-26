<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('campaigns')) {
            Schema::create('campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->foreignId('module_id')->nullable()->constrained('modules')->onDelete('set null');
                $table->tinyInteger('status')->default(1);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->timestamps();

                $table->index('module_id', 'idx_campaigns_module_id');
                $table->index('status', 'idx_campaigns_status');
                $table->index('start_date', 'idx_campaigns_start_date');
                $table->index('end_date', 'idx_campaigns_end_date');
            });
        }

        if (!Schema::hasTable('campaign_store')) {
            Schema::create('campaign_store', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
                $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['campaign_id', 'store_id'], 'unique_campaign_store');
                $table->index('campaign_id', 'idx_campaign_store_campaign_id');
                $table->index('store_id', 'idx_campaign_store_store_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_store');
        Schema::dropIfExists('campaigns');
    }
};

