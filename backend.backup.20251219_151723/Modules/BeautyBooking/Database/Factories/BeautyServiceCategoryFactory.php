<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Database\Factories;

use Modules\BeautyBooking\Entities\BeautyServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Beauty Service Category Factory
 * Factory برای دسته‌بندی خدمات
 */
class BeautyServiceCategoryFactory extends Factory
{
    protected $model = BeautyServiceCategory::class;

    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => $this->faker->words(2, true) . ' Category',
            'image' => null,
            'status' => true,
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }
}

