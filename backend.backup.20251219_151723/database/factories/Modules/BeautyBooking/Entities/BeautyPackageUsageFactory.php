<?php

namespace Database\Factories\Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BeautyBooking\Entities\BeautyPackageUsage;

class BeautyPackageUsageFactory extends Factory
{
    protected $model = BeautyPackageUsage::class;

    public function definition(): array
    {
        return [
            'package_id' => \Modules\BeautyBooking\Entities\BeautyPackage::factory(),
            'booking_id' => null,
            'user_id' => \App\Models\User::factory(),
            'salon_id' => \Modules\BeautyBooking\Entities\BeautySalon::factory(),
            'session_number' => 1,
            'used_at' => now(),
            'status' => 'used',
        ];
    }
}

