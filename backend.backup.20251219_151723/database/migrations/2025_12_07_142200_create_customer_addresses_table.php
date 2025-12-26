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
        if (!Schema::hasTable('customer_addresses')) {
            Schema::create('customer_addresses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('contact_person_name')->nullable();
                $table->string('contact_person_number')->nullable();
                $table->string('address_type')->nullable();
                $table->text('address')->nullable();
                $table->string('floor')->nullable();
                $table->string('road')->nullable();
                $table->string('house')->nullable();
                $table->string('longitude', 50)->nullable();
                $table->string('latitude', 50)->nullable();
                $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
                $table->timestamps();

                $table->index('user_id', 'idx_customer_addresses_user_id');
                $table->index('zone_id', 'idx_customer_addresses_zone_id');
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
        Schema::dropIfExists('customer_addresses');
    }
};

