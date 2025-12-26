<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admin_wallets')) {
            Schema::create('admin_wallets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('admin_id')->unique();
                $table->decimal('balance', 23, 8)->default(0);
                $table->decimal('total_commission_earning', 23, 8)->default(0);
                $table->decimal('total_earning', 23, 8)->default(0);
                $table->decimal('collected_cash', 23, 8)->default(0);
                $table->decimal('pending_withdraw', 23, 8)->default(0);
                $table->timestamps();
                
                $table->index('admin_id', 'idx_admin_wallets_admin_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('admin_wallets')) {
            Schema::dropIfExists('admin_wallets');
        }
    }
};

