<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductIdToBeautyRetailOrdersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('beauty_retail_orders') && !Schema::hasColumn('beauty_retail_orders', 'product_id')) {
            Schema::table('beauty_retail_orders', function (Blueprint $table) {
                $table->foreignId('product_id')
                    ->nullable()
                    ->after('salon_id')
                    ->constrained('beauty_retail_products')
                    ->onDelete('set null');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('beauty_retail_orders') && Schema::hasColumn('beauty_retail_orders', 'product_id')) {
            Schema::table('beauty_retail_orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('product_id');
            });
        }
    }
}

