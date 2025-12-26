<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Database\Factories;

use Modules\BeautyBooking\Entities\BeautyService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Beauty Service Factory
 * Factory برای خدمات
 */
class BeautyServiceFactory extends Factory
{
    protected $model = BeautyService::class;

    public function definition(): array
    {
        return [
            'salon_id' => \Modules\BeautyBooking\Entities\BeautySalon::factory(),
            'category_id' => \Modules\BeautyBooking\Entities\BeautyServiceCategory::factory(),
            'name' => $this->faker->words(3, true) . ' Service',
            'description' => $this->faker->paragraph(),
            'duration_minutes' => $this->faker->numberBetween(30, 180),
            'price' => $this->faker->randomFloat(2, 50, 500),
            'image' => null,
            'status' => true,
            'staff_ids' => null,
            'service_type' => 'service',
            'consultation_credit_percentage' => 0.0,
        ];
    }
}

