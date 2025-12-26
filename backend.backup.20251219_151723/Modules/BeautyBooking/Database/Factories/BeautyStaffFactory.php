<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Database\Factories;

use Modules\BeautyBooking\Entities\BeautyStaff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Beauty Staff Factory
 * Factory برای کارمندان سالن
 */
class BeautyStaffFactory extends Factory
{
    protected $model = BeautyStaff::class;

    public function definition(): array
    {
        return [
            'salon_id' => \Modules\BeautyBooking\Entities\BeautySalon::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'avatar' => null,
            'status' => true,
            'specializations' => [$this->faker->word(), $this->faker->word()],
            'working_hours' => [
                'monday' => ['open' => '09:00', 'close' => '18:00'],
                'tuesday' => ['open' => '09:00', 'close' => '18:00'],
                'wednesday' => ['open' => '09:00', 'close' => '18:00'],
                'thursday' => ['open' => '09:00', 'close' => '18:00'],
                'friday' => ['open' => '09:00', 'close' => '18:00'],
                'saturday' => ['open' => '09:00', 'close' => '18:00'],
                'sunday' => null,
            ],
            'breaks' => null,
            'days_off' => null,
        ];
    }
}

