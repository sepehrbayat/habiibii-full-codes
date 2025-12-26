<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add session counters to beauty packages
 * افزودن شمارنده جلسات به پکیج‌ها
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * اجرای مهاجرت
     */
    public function up(): void
    {
        if (Schema::hasTable('beauty_packages')) {
            Schema::table('beauty_packages', function (Blueprint $table) {
                if (!Schema::hasColumn('beauty_packages', 'total_sessions')) {
                    $table->integer('total_sessions')->default(0)->after('sessions_count');
                }

                if (!Schema::hasColumn('beauty_packages', 'used_sessions')) {
                    $table->integer('used_sessions')->default(0)->after('total_sessions');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     * بازگردانی مهاجرت
     */
    public function down(): void
    {
        if (Schema::hasTable('beauty_packages')) {
            Schema::table('beauty_packages', function (Blueprint $table) {
                if (Schema::hasColumn('beauty_packages', 'used_sessions')) {
                    $table->dropColumn('used_sessions');
                }

                if (Schema::hasColumn('beauty_packages', 'total_sessions')) {
                    $table->dropColumn('total_sessions');
                }
            });
        }
    }
};

