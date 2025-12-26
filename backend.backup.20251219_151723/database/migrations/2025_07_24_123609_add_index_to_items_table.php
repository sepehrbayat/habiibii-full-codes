<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                foreach (['category_id', 'store_id', 'name', 'slug', 'price', 'created_at', 'order_count', 'avg_rating'] as $col) {
                    if (Schema::hasColumn('items', $col)) {
                        $table->index($col);
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                foreach (['category_id', 'store_id', 'name', 'slug', 'price', 'created_at', 'order_count', 'avg_rating'] as $col) {
                    if (Schema::hasColumn('items', $col)) {
                        $table->dropIndex([$col]);
                    }
                }
            });
        }
    }
};
