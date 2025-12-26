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
        if (Schema::hasTable('wishlists')) {
            Schema::table('wishlists', function (Blueprint $table) {
                foreach (['user_id', 'item_id', 'store_id'] as $col) {
                    if (Schema::hasColumn('wishlists', $col)) {
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
        if (Schema::hasTable('wishlists')) {
            Schema::table('wishlists', function (Blueprint $table) {
                foreach (['user_id', 'item_id', 'store_id'] as $col) {
                    if (Schema::hasColumn('wishlists', $col)) {
                        $table->dropIndex([$col]);
                    }
                }
            });
        }
    }
};
