<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BeautyBooking\Entities\BeautyRetailOrder;

class BeautyRetailOrderFactory extends Factory
{
    protected $model = BeautyRetailOrder::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'salon_id' => \Modules\BeautyBooking\Entities\BeautySalon::factory(),
            'zone_id' => null,
            'order_reference' => $this->faker->unique()->uuid(),
            'products' => [],
            'subtotal' => 0,
            'tax_amount' => 0,
            'shipping_fee' => 0,
            'discount' => 0,
            'total_amount' => 0,
            'commission_amount' => 0,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => null,
            'shipping_address' => null,
            'shipping_phone' => null,
            'notes' => null,
        ];
    }
}

