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
        if (Schema::hasTable('items') && !Schema::hasColumn('items', 'module_id')) {
            Schema::table('items', function (Blueprint $table) {
                $table->unsignedBigInteger('module_id')
                    ->nullable()
                    ->after('category_id');
                $table->index('module_id', 'idx_items_module_id');

                if (Schema::hasTable('modules')) {
                    $table->foreign('module_id')
                        ->references('id')
                        ->on('modules')
                        ->onDelete('set null');
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
        if (Schema::hasTable('items') && Schema::hasColumn('items', 'module_id')) {
            Schema::table('items', function (Blueprint $table) {
                if (Schema::hasTable('modules')) {
                    $table->dropForeign(['module_id']);
                }
                $table->dropIndex('idx_items_module_id');
                $table->dropColumn('module_id');
            });
        }
    }
};

