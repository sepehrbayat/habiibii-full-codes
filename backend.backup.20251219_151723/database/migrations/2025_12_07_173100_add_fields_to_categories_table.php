<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (!Schema::hasColumn('categories', 'image')) {
                    $table->string('image')->nullable()->after('name');
                }
                if (!Schema::hasColumn('categories', 'priority')) {
                    $table->integer('priority')->default(0)->after('image');
                    $table->index('priority', 'idx_categories_priority');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (Schema::hasColumn('categories', 'priority')) {
                    $table->dropIndex('idx_categories_priority');
                    $table->dropColumn('priority');
                }
                if (Schema::hasColumn('categories', 'image')) {
                    $table->dropColumn('image');
                }
            });
        }
    }
};

