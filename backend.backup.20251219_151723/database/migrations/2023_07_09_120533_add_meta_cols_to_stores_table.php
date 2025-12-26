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
        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $table) {
                if (!Schema::hasColumn('stores', 'meta_title')) {
                    $table->string('meta_title', 100)->nullable();
                }
                if (!Schema::hasColumn('stores', 'meta_description')) {
                    $table->text('meta_description')->nullable();
                }
                if (!Schema::hasColumn('stores', 'meta_image')) {
                    $table->string('meta_image', 100)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $table) {
                $drops = [];
                foreach (['meta_title', 'meta_description', 'meta_image'] as $col) {
                    if (Schema::hasColumn('stores', $col)) {
                        $drops[] = $col;
                    }
                }
                if ($drops) {
                    $table->dropColumn($drops);
                }
            });
        }
    }
};
