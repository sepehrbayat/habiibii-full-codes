<?php

namespace Database\Factories\Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BeautyBooking\Entities\BeautyCalendarBlock;

class BeautyCalendarBlockFactory extends Factory
{
    protected $model = BeautyCalendarBlock::class;

    public function definition(): array
    {
        $date = $this->faker->date('Y-m-d');
        return [
            'salon_id' => \Modules\BeautyBooking\Entities\BeautySalon::factory(),
            'staff_id' => null,
            'date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'type' => 'manual_block',
            'reason' => $this->faker->sentence(),
        ];
    }
}

