<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add Loyalty Campaign to Transaction Types Migration
 * Migration برای افزودن کمپین وفاداری به انواع تراکنش
 */
class AddLoyaltyCampaignToTransactionTypes extends Migration
{
    /**
     * Run the migrations.
     * اجرای migration
     *
     * @return void
     */
    public function up()
    {
        // Modify enum to include loyalty_campaign
        // تغییر enum برای شامل کردن loyalty_campaign
        DB::statement("ALTER TABLE beauty_transactions MODIFY COLUMN transaction_type ENUM(
            'commission',
            'subscription',
            'advertisement',
            'service_fee',
            'package_sale',
            'cancellation_fee',
            'consultation_fee',
            'cross_selling',
            'retail_sale',
            'gift_card_sale',
            'loyalty_campaign'
        ) DEFAULT 'commission'");
    }

    /**
     * Reverse the migrations.
     * برگشت migration
     *
     * @return void
     */
    public function down()
    {
        // Remove loyalty_campaign from enum
        // حذف loyalty_campaign از enum
        DB::statement("ALTER TABLE beauty_transactions MODIFY COLUMN transaction_type ENUM(
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
        ) DEFAULT 'commission'");
    }
}

