<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('beauty_gift_cards') && !Schema::hasColumn('beauty_gift_cards', 'redeemer_id')) {
            Schema::table('beauty_gift_cards', function (Blueprint $table) {
                $table->foreignId('redeemer_id')
                    ->nullable()
                    ->constrained('users')
                    ->onDelete('set null')
                    ->after('purchaser_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('beauty_gift_cards') && Schema::hasColumn('beauty_gift_cards', 'redeemer_id')) {
            Schema::table('beauty_gift_cards', function (Blueprint $table) {
                $table->dropForeign(['redeemer_id']);
                $table->dropColumn('redeemer_id');
            });
        }
    }
};

