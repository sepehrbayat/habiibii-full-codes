<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add Conversation ID to Beauty Bookings Table Migration
 * Migration برای افزودن شناسه گفتگو به جدول رزروها
 */
class AddConversationIdToBeautyBookingsTable extends Migration
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
            // Conversation ID for internal chat between customer and salon
            // شناسه گفتگو برای چت داخلی بین مشتری و سالن
            $table->foreignId('conversation_id')
                ->nullable()
                ->after('zone_id')
                ->constrained('conversations')
                ->onDelete('set null');
            
            // Index for conversation queries
            $table->index('conversation_id', 'idx_beauty_bookings_conversation_id');
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
            $table->dropForeign(['conversation_id']);
            $table->dropIndex('idx_beauty_bookings_conversation_id');
            $table->dropColumn('conversation_id');
        });
    }
}

