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
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'cm_firebase_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('cm_firebase_token')->nullable()->after('login_medium');
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
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'cm_firebase_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('cm_firebase_token');
            });
        }
    }
};

