<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('store_wallets')) {
            Schema::create('store_wallets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('vendor_id')->unique();
                $table->decimal('balance', 23, 8)->default(0);
                $table->decimal('total_earning', 23, 8)->default(0);
                $table->decimal('collected_cash', 23, 8)->default(0);
                $table->decimal('pending_withdraw', 23, 8)->default(0);
                $table->timestamps();
                
                $table->index('vendor_id', 'idx_store_wallets_vendor_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('store_wallets')) {
            Schema::dropIfExists('store_wallets');
        }
    }
};

