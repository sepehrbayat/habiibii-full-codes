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
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (!Schema::hasColumn('items', 'price')) {
                    $table->decimal('price', 23, 2)->default(0)->after('name');
                    $table->index('price', 'idx_items_price');
                }
                if (!Schema::hasColumn('items', 'discount')) {
                    $table->decimal('discount', 23, 2)->default(0)->after('price');
                }
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
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (Schema::hasColumn('items', 'price')) {
                    $table->dropIndex('idx_items_price');
                    $table->dropColumn('price');
                }
                if (Schema::hasColumn('items', 'discount')) {
                    $table->dropColumn('discount');
                }
            });
        }
    }
};

