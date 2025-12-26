<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Transactions Table Migration
 * Migration برای ایجاد جدول تراکنش‌های مالی
 */
class CreateBeautyTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_transactions', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('booking_id')
                ->nullable()
                ->constrained('beauty_bookings')
                ->onDelete('set null');
            $table->foreignId('salon_id')
                ->constrained('beauty_salons')
                ->onDelete('cascade');
            $table->foreignId('zone_id')
                ->nullable()
                ->constrained('zones')
                ->onDelete('set null');
            
            // Transaction details
            $table->enum('transaction_type', [
                'commission',
                'subscription',
                'advertisement',
                'service_fee',
                'package_sale',
                'cancellation_fee',
                'consultation_fee',
                'cross_selling',
                'retail_sale',
                'gift_card_sale'
            ])->default('commission');
            
            $table->decimal('amount', 23, 8)->default(0.00);
            $table->decimal('commission', 23, 8)->default(0.00);
            $table->decimal('service_fee', 23, 8)->default(0.00);
            $table->string('reference_number', 100)->nullable();
            
            // Status
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('booking_id', 'idx_beauty_transactions_booking_id');
            $table->index('salon_id', 'idx_beauty_transactions_salon_id');
            $table->index('transaction_type', 'idx_beauty_transactions_transaction_type');
            $table->index('status', 'idx_beauty_transactions_status');
            $table->index('reference_number', 'idx_beauty_transactions_reference_number');
            $table->index(['salon_id', 'transaction_type'], 'idx_beauty_transactions_salon_type');
            $table->index(['status', 'created_at'], 'idx_beauty_transactions_status_created');
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
        Schema::dropIfExists('beauty_transactions');
    }
}

