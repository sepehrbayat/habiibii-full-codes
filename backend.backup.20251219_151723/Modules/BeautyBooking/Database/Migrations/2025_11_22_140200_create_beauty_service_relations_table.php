<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create Beauty Service Relations Table Migration
 * Migration برای ایجاد جدول روابط خدمات
 */
class CreateBeautyServiceRelationsTable extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beauty_service_relations', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('service_id')
                ->constrained('beauty_services')
                ->onDelete('cascade');
            $table->foreignId('related_service_id')
                ->constrained('beauty_services')
                ->onDelete('cascade');
            
            // Relation type: 'complementary' (suggested together), 'upsell' (upgrade option)
            // نوع رابطه: 'complementary' (پیشنهاد شده با هم)، 'upsell' (گزینه ارتقا)
            $table->enum('relation_type', ['complementary', 'upsell'])
                ->default('complementary');
            
            // Priority/weight for sorting suggestions (higher = more important)
            // اولویت/وزن برای مرتب‌سازی پیشنهادها (بالاتر = مهم‌تر)
            $table->integer('priority')->default(0);
            
            // Whether this relation is active
            // آیا این رابطه فعال است
            $table->boolean('status')->default(1);
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('service_id', 'idx_beauty_service_relations_service_id');
            $table->index('related_service_id', 'idx_beauty_service_relations_related_service_id');
            $table->index('relation_type', 'idx_beauty_service_relations_relation_type');
            $table->index(['service_id', 'relation_type'], 'idx_beauty_service_relations_service_type');
            
            // Unique constraint: same service cannot have duplicate relations
            // محدودیت یکتا: همان خدمت نمی‌تواند روابط تکراری داشته باشد
            $table->unique(['service_id', 'related_service_id', 'relation_type'], 'unique_service_relation');
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
        Schema::dropIfExists('beauty_service_relations');
    }
}

