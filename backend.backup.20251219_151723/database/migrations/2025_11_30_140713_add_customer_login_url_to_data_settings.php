<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\CentralLogics\Helpers;

/**
 * Add customer login URL to data settings
 * افزودن URL ورود مشتری به تنظیمات داده
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up(): void
    {
        // Insert customer login URL setting if it doesn't exist
        // درج تنظیم URL ورود مشتری در صورت عدم وجود
        Helpers::insert_data_settings_key('customer_login_url', 'login_customer', 'customer');
    }

    /**
     * Reverse the migrations.
     * برگشت migration
     *
     * @return void
     */
    public function down(): void
    {
        // Remove customer login URL setting
        // حذف تنظیم URL ورود مشتری
        DB::table('data_settings')
            ->where('key', 'customer_login_url')
            ->where('type', 'login_customer')
            ->delete();
    }
};
