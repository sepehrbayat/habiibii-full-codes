<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('beauty_bookings') && !Schema::hasColumn('beauty_bookings', 'duration_minutes')) {
            Schema::table('beauty_bookings', function (Blueprint $table) {
                $table->integer('duration_minutes')->default(30)->after('cancelled_by');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('beauty_bookings') && Schema::hasColumn('beauty_bookings', 'duration_minutes')) {
            Schema::table('beauty_bookings', function (Blueprint $table) {
                $table->dropColumn('duration_minutes');
            });
        }
    }
};

