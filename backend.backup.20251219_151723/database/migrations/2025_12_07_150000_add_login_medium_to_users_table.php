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
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'login_medium')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('login_medium')->nullable()->after('temp_token');
                $table->index('login_medium', 'idx_users_login_medium');
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
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'login_medium')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('idx_users_login_medium');
                $table->dropColumn('login_medium');
            });
        }
    }
};

