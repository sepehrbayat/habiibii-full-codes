<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        if (Schema::hasTable('items') && !Schema::hasColumn('items', 'store_id')) {
            Schema::table('items', function (Blueprint $table) {
                $table->unsignedBigInteger('store_id')
                    ->nullable()
                    ->after('id');
                $table->index('store_id', 'idx_items_store_id');

                if (Schema::hasTable('stores')) {
                    $table->foreign('store_id')
                        ->references('id')
                        ->on('stores')
                        ->onDelete('set null');
                }
            });

            // Optional backfill: set store_id to first store if exists
            if (Schema::hasTable('stores')) {
                $firstStoreId = DB::table('stores')->orderBy('id')->value('id');
                if ($firstStoreId) {
                    DB::table('items')
                        ->whereNull('store_id')
                        ->update(['store_id' => $firstStoreId]);
                }
            }
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
        if (Schema::hasTable('items') && Schema::hasColumn('items', 'store_id')) {
            Schema::table('items', function (Blueprint $table) {
                if (Schema::hasTable('stores')) {
                    $table->dropForeign(['store_id']);
                }
                $table->dropIndex('idx_items_store_id');
                $table->dropColumn('store_id');
            });
        }
    }
};

