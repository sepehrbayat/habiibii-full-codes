<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value')->nullable();
                $table->integer('expiration');
                $table->index('expiration');
            });
        }
    }

    /**
     * Reverse the migrations.
     * بازگشت migration
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
    }
};

