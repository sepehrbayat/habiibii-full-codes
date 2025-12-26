<?php

declare(strict_types=1);

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
        if (Schema::hasTable('beauty_gift_cards') && !Schema::hasColumn('beauty_gift_cards', 'purchaser_id')) {
            Schema::table('beauty_gift_cards', function (Blueprint $table) {
                $table->foreignId('purchaser_id')
                    ->nullable()
                    ->constrained('users')
                    ->onDelete('set null')
                    ->after('redeemed_by');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('beauty_gift_cards') && Schema::hasColumn('beauty_gift_cards', 'purchaser_id')) {
            Schema::table('beauty_gift_cards', function (Blueprint $table) {
                $table->dropConstrainedForeignId('purchaser_id');
            });
        }
    }
};

