<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('vendors') && !Schema::hasColumn('vendors', 'store_id')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->foreignId('store_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('stores')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     * بازگشت migration
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasTable('vendors') && Schema::hasColumn('vendors', 'store_id')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->dropConstrainedForeignId('store_id');
            });
        }
    }
};

