<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (!Schema::hasColumn('items', 'category_ids')) {
                    $table->json('category_ids')->nullable()->after('category_id');
                }
                if (!Schema::hasColumn('items', 'order_count')) {
                    $table->integer('order_count')->default(0)->after('is_halal');
                    $table->index('order_count', 'idx_items_order_count');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (Schema::hasColumn('items', 'category_ids')) {
                    $table->dropColumn('category_ids');
                }
                if (Schema::hasColumn('items', 'order_count')) {
                    $table->dropIndex('idx_items_order_count');
                    $table->dropColumn('order_count');
                }
            });
        }
    }
};

