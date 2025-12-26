<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToBeautyBadgesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('beauty_badges') && !Schema::hasColumn('beauty_badges', 'status')) {
            Schema::table('beauty_badges', function (Blueprint $table) {
                $table->boolean('status')->default(1)->after('badge_type');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('beauty_badges') && Schema::hasColumn('beauty_badges', 'status')) {
            Schema::table('beauty_badges', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
}

