<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BeautyBooking\Entities\BeautyRetailProduct;

class BeautyRetailProductFactory extends Factory
{
    protected $model = BeautyRetailProduct::class;

    public function definition(): array
    {
        return [
            'salon_id' => \Modules\BeautyBooking\Entities\BeautySalon::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 200),
            'image' => null,
            'category' => 'general',
            'stock_quantity' => $this->faker->numberBetween(1, 50),
            'min_stock_level' => 0,
            'status' => 1,
        ];
    }
}

