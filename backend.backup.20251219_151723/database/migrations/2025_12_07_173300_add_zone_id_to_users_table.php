<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'zone_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('zone_id')->nullable()->after('module_ids');
                $table->index('zone_id', 'idx_users_zone_id');
                if (Schema::hasTable('zones')) {
                    $table->foreign('zone_id')->references('id')->on('zones')->onDelete('set null');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'zone_id')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasTable('zones')) {
                    $table->dropForeign(['zone_id']);
                }
                $table->dropIndex('idx_users_zone_id');
                $table->dropColumn('zone_id');
            });
        }
    }
};

