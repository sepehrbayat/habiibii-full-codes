<?php

namespace Database\Factories\Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BeautyBooking\Entities\BeautySubscription;

class BeautySubscriptionFactory extends Factory
{
    protected $model = BeautySubscription::class;

    public function definition(): array
    {
        return [
            'salon_id' => \Modules\BeautyBooking\Entities\BeautySalon::factory(),
            'subscription_type' => 'featured_listing',
            'duration_days' => 30,
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'amount_paid' => 0,
            'status' => 'active',
        ];
    }
}

