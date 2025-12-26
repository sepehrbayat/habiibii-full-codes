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
        if (Schema::hasTable('banners') && !Schema::hasColumn('banners', 'created_by')) {
            Schema::table('banners', function (Blueprint $table) {
                $table->string('created_by')->nullable()->after('status');
                $table->index('created_by', 'idx_banners_created_by');
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
        if (Schema::hasTable('banners') && Schema::hasColumn('banners', 'created_by')) {
            Schema::table('banners', function (Blueprint $table) {
                $table->dropIndex('idx_banners_created_by');
                $table->dropColumn('created_by');
            });
        }
    }
};

