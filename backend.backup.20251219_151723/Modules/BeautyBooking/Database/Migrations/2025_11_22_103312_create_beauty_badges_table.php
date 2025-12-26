<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Badges Table Migration
 * Migration برای ایجاد جدول نشان‌ها (Badge)
 */
class CreateBeautyBadgesTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_badges', function (Blueprint $table) {
            $table->id();
            
            // Foreign key
            $table->foreignId('salon_id')
                ->constrained('beauty_salons')
                ->onDelete('cascade');
            
            // Badge information
            $table->enum('badge_type', ['top_rated', 'featured', 'verified'])->default('top_rated');
            $table->dateTime('earned_at');
            $table->dateTime('expires_at')->nullable(); // null means permanent (for verified)
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('salon_id', 'idx_beauty_badges_salon_id');
            $table->index('badge_type', 'idx_beauty_badges_badge_type');
            $table->index('expires_at', 'idx_beauty_badges_expires_at');
            $table->index(['salon_id', 'badge_type'], 'idx_beauty_badges_salon_type');
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
        Schema::dropIfExists('beauty_badges');
    }
}

