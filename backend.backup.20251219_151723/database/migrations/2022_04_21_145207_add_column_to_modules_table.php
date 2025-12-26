<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('modules', 'icon') || !Schema::hasColumn('modules', 'theme_id') || !Schema::hasColumn('modules', 'description')) {
            Schema::table('modules', function (Blueprint $table) {
                if (!Schema::hasColumn('modules', 'icon')) {
                    $table->string('icon', 191)->nullable();
                }
                if (!Schema::hasColumn('modules', 'theme_id')) {
                    $table->integer('theme_id')->default(1);
                }
                if (!Schema::hasColumn('modules', 'description')) {
                    $table->text('description')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
            if (Schema::hasColumn('modules', 'icon')) {
                $table->dropColumn('icon');
            }
            if (Schema::hasColumn('modules', 'theme_id')) {
                $table->dropColumn('theme_id');
            }
            if (Schema::hasColumn('modules', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
}
