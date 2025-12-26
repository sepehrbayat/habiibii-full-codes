<?php

namespace Database\Factories\Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BeautyBooking\Entities\BeautyBadge;

class BeautyBadgeFactory extends Factory
{
    protected $model = BeautyBadge::class;

    public function definition(): array
    {
        return [
            'salon_id' => \Modules\BeautyBooking\Entities\BeautySalon::factory(),
            'badge_type' => 'featured',
            'earned_at' => now(),
            'expires_at' => now()->addDays(30),
        ];
    }
}

