<?php

namespace Database\Factories\Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BeautyBooking\Entities\BeautyLoyaltyPoint;

class BeautyLoyaltyPointFactory extends Factory
{
    protected $model = BeautyLoyaltyPoint::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'booking_id' => \Modules\BeautyBooking\Entities\BeautyBooking::factory(),
            'salon_id' => \Modules\BeautyBooking\Entities\BeautySalon::factory(),
            'campaign_id' => null,
            'points' => 10,
            'type' => 'earned',
            'description' => null,
            'expires_at' => now()->addDays(60),
        ];
    }
}

