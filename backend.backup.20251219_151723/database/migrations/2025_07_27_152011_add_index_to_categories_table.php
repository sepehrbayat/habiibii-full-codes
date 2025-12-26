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
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                foreach (['parent_id', 'name'] as $col) {
                    if (Schema::hasColumn('categories', $col)) {
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
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                foreach (['parent_id', 'name'] as $col) {
                    if (Schema::hasColumn('categories', $col)) {
                        $table->dropIndex([$col]);
                    }
                }
            });
        }
    }
};
