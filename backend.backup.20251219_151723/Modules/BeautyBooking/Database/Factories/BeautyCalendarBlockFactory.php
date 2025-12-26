<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BeautyBooking\Entities\BeautyCalendarBlock;

class BeautyCalendarBlockFactory extends Factory
{
    protected $model = BeautyCalendarBlock::class;

    public function definition(): array
    {
        $date = $this->faker->date('Y-m-d');
        $start = $this->faker->time('H:i:s');
        $end = $this->faker->time('H:i:s');

        return [
            'salon_id' => \Modules\BeautyBooking\Entities\BeautySalon::factory(),
            'staff_id' => null,
            'date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'type' => 'manual_block',
            'reason' => $this->faker->sentence(),
        ];
    }
}

