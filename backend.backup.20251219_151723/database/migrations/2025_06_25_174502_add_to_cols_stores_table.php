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
                if (!Schema::hasColumn('stores', 'tin')) {
                    $table->string('tin')->nullable();
                }
                if (!Schema::hasColumn('stores', 'tin_expire_date')) {
                    $table->date('tin_expire_date')->nullable();
                }
                if (!Schema::hasColumn('stores', 'tin_certificate_image')) {
                    $table->string('tin_certificate_image')->nullable();
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
                foreach (['tin', 'tin_expire_date', 'tin_certificate_image'] as $col) {
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
