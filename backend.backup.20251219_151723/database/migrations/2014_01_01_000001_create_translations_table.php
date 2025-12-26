<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('translations')) {
            Schema::create('translations', function (Blueprint $table) {
                $table->id();
                $table->string('translationable_type')->index();
                $table->unsignedBigInteger('translationable_id')->index();
                $table->string('locale', 10)->index()->default('en');
                $table->string('key')->index()->nullable();
                $table->text('value')->nullable();
                $table->timestamps();
                $table->index(['translationable_type', 'translationable_id'], 'translations_type_id_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translations');
    }
}

