<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulesTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('modules')) {
            Schema::create('modules', function (Blueprint $table) {
                $table->id();
                $table->string('module_name', 100)->nullable();
                $table->string('module_type', 100)->nullable();
                $table->string('thumbnail', 255)->nullable();
                $table->string('icon', 255)->nullable();
                $table->tinyInteger('status')->default(1);
                $table->integer('stores_count')->default(0);
                $table->integer('theme_id')->default(1);
                $table->text('description')->nullable();
                $table->tinyInteger('all_zone_service')->default(0);
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
        Schema::dropIfExists('modules');
    }
}

