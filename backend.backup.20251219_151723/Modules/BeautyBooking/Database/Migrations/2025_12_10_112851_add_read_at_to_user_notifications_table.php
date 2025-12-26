<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     */
    public function up(): void
    {
        if (Schema::hasTable('user_notifications') && !Schema::hasColumn('user_notifications', 'read_at')) {
        Schema::table('user_notifications', function (Blueprint $table) {
                $table->timestamp('read_at')->nullable()->after('updated_at');
        });
        }
    }

    /**
     * Reverse the migrations.
     * برگشت migration
     */
    public function down(): void
    {
        if (Schema::hasTable('user_notifications') && Schema::hasColumn('user_notifications', 'read_at')) {
        Schema::table('user_notifications', function (Blueprint $table) {
                $table->dropColumn('read_at');
        });
        }
    }
};
