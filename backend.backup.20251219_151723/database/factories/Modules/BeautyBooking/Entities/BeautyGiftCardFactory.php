<?php

namespace Database\Factories\Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BeautyBooking\Entities\BeautyGiftCard;

class BeautyGiftCardFactory extends Factory
{
    protected $model = BeautyGiftCard::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('GC-#######'),
            'salon_id' => \Modules\BeautyBooking\Entities\BeautySalon::factory(),
            'purchased_by' => \App\Models\User::factory(),
            'redeemed_by' => null,
            'purchaser_id' => \App\Models\User::factory(),
            'amount' => 100,
            'status' => 'active',
            'expires_at' => now()->addDays(30),
            'redeemed_at' => null,
        ];
    }
}

