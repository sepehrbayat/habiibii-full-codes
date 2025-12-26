<?php

namespace Database\Factories\Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BeautyBooking\Entities\BeautyLoyaltyCampaign;

class BeautyLoyaltyCampaignFactory extends Factory
{
    protected $model = BeautyLoyaltyCampaign::class;

    public function definition(): array
    {
        return [
            'salon_id' => \Modules\BeautyBooking\Entities\BeautySalon::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'type' => 'points',
            'rules' => ['points_per_currency' => 1],
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => true,
            'commission_percentage' => 0,
            'commission_type' => 'percentage',
            'total_participants' => 0,
            'total_redeemed' => 0,
            'total_revenue' => 0,
        ];
    }
}

