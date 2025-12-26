<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add Package ID to Beauty Bookings Table Migration
 * Migration برای افزودن شناسه پکیج به جدول رزروها
 */
class AddPackageIdToBeautyBookingsTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::table('beauty_bookings', function (Blueprint $table) {
            // Package ID if this booking uses a package
            // شناسه پکیج در صورت استفاده از پکیج در این رزرو
            $table->foreignId('package_id')
                ->nullable()
                ->after('service_id')
                ->constrained('beauty_packages')
                ->onDelete('set null');
            
            // Index for package queries
            $table->index('package_id', 'idx_beauty_bookings_package_id');
        });
    }

    /**
     * Reverse the migrations.
     * برگشت migration
     *
     * @return void
     */
    public function down()
    {
        Schema::table('beauty_bookings', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropIndex('idx_beauty_bookings_package_id');
            $table->dropColumn('package_id');
        });
    }
}

