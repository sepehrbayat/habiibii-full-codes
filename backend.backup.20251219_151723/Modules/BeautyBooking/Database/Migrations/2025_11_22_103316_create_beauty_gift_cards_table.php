<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Gift Cards Table Migration
 * Migration برای ایجاد جدول کارت‌های هدیه
 */
class CreateBeautyGiftCardsTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_gift_cards', function (Blueprint $table) {
            $table->id();
            
            // Gift card code
            $table->string('code', 50)->unique();
            
            // Foreign keys
            $table->foreignId('salon_id')
                ->nullable()
                ->constrained('beauty_salons')
                ->onDelete('set null');
            $table->foreignId('purchased_by')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('redeemed_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            
            // Gift card details
            $table->decimal('amount', 23, 8)->default(0.00);
            $table->enum('status', ['active', 'redeemed', 'expired', 'cancelled'])->default('active');
            $table->date('expires_at')->nullable();
            $table->dateTime('redeemed_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('code', 'idx_beauty_gift_cards_code');
            $table->index('salon_id', 'idx_beauty_gift_cards_salon_id');
            $table->index('purchased_by', 'idx_beauty_gift_cards_purchased_by');
            $table->index('redeemed_by', 'idx_beauty_gift_cards_redeemed_by');
            $table->index('status', 'idx_beauty_gift_cards_status');
            $table->index('expires_at', 'idx_beauty_gift_cards_expires_at');
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
        Schema::dropIfExists('beauty_gift_cards');
    }
}

