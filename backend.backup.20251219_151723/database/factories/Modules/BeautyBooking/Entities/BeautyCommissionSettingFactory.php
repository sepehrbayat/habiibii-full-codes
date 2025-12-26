<?php

namespace Database\Factories\Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BeautyBooking\Entities\BeautyCommissionSetting;

class BeautyCommissionSettingFactory extends Factory
{
    protected $model = BeautyCommissionSetting::class;

    public function definition(): array
    {
        return [
            'service_category_id' => \Modules\BeautyBooking\Entities\BeautyServiceCategory::factory(),
            'salon_level' => 'salon',
            'commission_percentage' => 10.0,
            'min_commission' => 0,
            'max_commission' => null,
            'status' => 1,
        ];
    }
}

