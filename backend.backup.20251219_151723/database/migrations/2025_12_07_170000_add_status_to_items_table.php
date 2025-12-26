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
        if (Schema::hasTable('items') && !Schema::hasColumn('items', 'status')) {
            Schema::table('items', function (Blueprint $table) {
                $table->tinyInteger('status')
                    ->default(1)
                    ->after('module_id');
                $table->index('status', 'idx_items_status');
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
        if (Schema::hasTable('items') && Schema::hasColumn('items', 'status')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropIndex('idx_items_status');
                $table->dropColumn('status');
            });
        }
    }
};

