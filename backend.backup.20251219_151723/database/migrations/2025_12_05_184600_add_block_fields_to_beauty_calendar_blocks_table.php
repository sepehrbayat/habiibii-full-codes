<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('beauty_calendar_blocks')) {
            Schema::table('beauty_calendar_blocks', function (Blueprint $table) {
                if (!Schema::hasColumn('beauty_calendar_blocks', 'block_date')) {
                    $table->date('block_date')->nullable()->after('reason');
                }
                if (!Schema::hasColumn('beauty_calendar_blocks', 'block_type')) {
                    $table->string('block_type', 50)->nullable()->after('block_date');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('beauty_calendar_blocks')) {
            Schema::table('beauty_calendar_blocks', function (Blueprint $table) {
                if (Schema::hasColumn('beauty_calendar_blocks', 'block_type')) {
                    $table->dropColumn('block_type');
                }
                if (Schema::hasColumn('beauty_calendar_blocks', 'block_date')) {
                    $table->dropColumn('block_date');
                }
            });
        }
    }
};

