<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZonesTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('zones')) {
            Schema::create('zones', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255)->nullable();
                $table->string('display_name', 255)->nullable();
                $table->text('coordinates')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->string('store_wise_topic', 255)->nullable();
                $table->string('customer_wise_topic', 255)->nullable();
                $table->string('deliveryman_wise_topic', 255)->nullable();
                $table->tinyInteger('cash_on_delivery')->default(1);
                $table->tinyInteger('digital_payment')->default(1);
                $table->tinyInteger('offline_payment')->default(0);
                $table->decimal('increased_delivery_fee', 23, 2)->default(0);
                $table->tinyInteger('increased_delivery_fee_status')->default(0);
                $table->text('increase_delivery_charge_message')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     * برگشت migration
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zones');
    }
}

