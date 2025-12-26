<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('stores')) {
            Schema::create('stores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->nullable()->constrained('vendors')->onDelete('cascade');
                $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
                $table->foreignId('module_id')->nullable()->constrained('modules')->onDelete('set null');
                $table->string('name', 255)->nullable();
                $table->string('phone', 20)->nullable();
                $table->string('email', 100)->nullable();
                $table->string('logo', 255)->nullable();
                $table->string('cover_photo', 255)->nullable();
                $table->text('address')->nullable();
                $table->string('latitude', 50)->nullable();
                $table->string('longitude', 50)->nullable();
                $table->text('footer_text')->nullable();
                $table->decimal('minimum_order', 23, 2)->default(0);
                $table->decimal('comission', 23, 2)->default(0);
                $table->tinyInteger('schedule_order')->default(0);
                $table->tinyInteger('status')->default(1);
                $table->tinyInteger('free_delivery')->default(0);
                $table->string('rating', 10)->nullable();
                $table->tinyInteger('delivery')->default(1);
                $table->tinyInteger('take_away')->default(0);
                $table->tinyInteger('item_section')->default(1);
                $table->decimal('tax', 23, 2)->default(0);
                $table->tinyInteger('reviews_section')->default(1);
                $table->tinyInteger('active')->default(1);
                $table->string('off_day', 20)->nullable();
                $table->string('gst', 50)->nullable();
                $table->tinyInteger('self_delivery_system')->default(0);
                $table->tinyInteger('pos_system')->default(0);
                $table->decimal('minimum_shipping_charge', 23, 2)->default(0);
                $table->string('delivery_time', 50)->nullable();
                $table->tinyInteger('veg')->default(0);
                $table->tinyInteger('non_veg')->default(0);
                $table->integer('order_count')->default(0);
                $table->integer('total_order')->default(0);
                $table->json('pickup_zone_id')->nullable();
                $table->integer('order_place_to_schedule_interval')->default(0);
                $table->tinyInteger('featured')->default(0);
                $table->decimal('per_km_shipping_charge', 23, 2)->default(0);
                $table->tinyInteger('prescription_order')->default(0);
                $table->string('slug', 255)->nullable();
                $table->decimal('maximum_shipping_charge', 23, 2)->default(0);
                $table->tinyInteger('cutlery')->default(0);
                $table->string('meta_title', 255)->nullable();
                $table->text('meta_description')->nullable();
                $table->string('meta_image', 255)->nullable();
                $table->tinyInteger('announcement')->default(0);
                $table->text('announcement_message')->nullable();
                $table->text('comment')->nullable();
                $table->string('tin', 50)->nullable();
                $table->date('tin_expire_date')->nullable();
                $table->string('tin_certificate_image', 255)->nullable();
                $table->string('store_business_model', 50)->default('none');
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
        Schema::dropIfExists('stores');
    }
}

