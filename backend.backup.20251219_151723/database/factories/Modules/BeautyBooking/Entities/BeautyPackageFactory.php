<?php

namespace Database\Factories\Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BeautyBooking\Entities\BeautyPackage;

class BeautyPackageFactory extends Factory
{
    protected $model = BeautyPackage::class;

    public function definition(): array
    {
        return [
            'salon_id' => \Modules\BeautyBooking\Entities\BeautySalon::factory(),
            'service_id' => \Modules\BeautyBooking\Entities\BeautyService::factory(),
            'name' => $this->faker->words(3, true),
            'sessions_count' => 5,
            'total_sessions' => 5,
            'used_sessions' => 0,
            'total_price' => 100,
            'discount_percentage' => 0,
            'validity_days' => 30,
            'status' => 1,
        ];
    }
}

