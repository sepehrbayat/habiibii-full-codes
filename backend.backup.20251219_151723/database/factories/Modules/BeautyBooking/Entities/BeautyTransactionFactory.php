<?php

namespace Database\Factories\Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BeautyBooking\Entities\BeautyTransaction;

class BeautyTransactionFactory extends Factory
{
    protected $model = BeautyTransaction::class;

    public function definition(): array
    {
        return [
            'booking_id' => null,
            'salon_id' => \Modules\BeautyBooking\Entities\BeautySalon::factory(),
            'zone_id' => null,
            'transaction_type' => 'commission',
            'amount' => 0,
            'commission' => 0,
            'service_fee' => 0,
            'reference_number' => $this->faker->uuid(),
            'status' => 'pending',
            'notes' => null,
        ];
    }
}

