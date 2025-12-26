<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        if (!Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->string('country')->nullable();
                $table->string('currency_code', 10)->unique();
                $table->string('currency_symbol', 10)->nullable();
                $table->decimal('exchange_rate', 24, 3)->default(1.000);
                $table->timestamps();
            });

            // Seed a default currency if table was just created
            DB::table('currencies')->insert([
                'country' => 'Default',
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'exchange_rate' => 1.000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
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
        Schema::dropIfExists('currencies');
    }
};

