<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->email(),
            'logo' => null,
            'cover_photo' => null,
            'address' => $this->faker->address(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'vendor_id' => \App\Models\Vendor::factory(),
            'zone_id' => null,
            'module_id' => null,
            'minimum_order' => 0,
            'comission' => 0,
            'delivery_time' => '30-40 min',
            'minimum_shipping_charge' => 0,
            'per_km_shipping_charge' => 0,
            'maximum_shipping_charge' => 0,
            'schedule_order' => 0,
            'status' => 1,
            'self_delivery_system' => 0,
            'veg' => 0,
            'non_veg' => 0,
            'free_delivery' => 0,
            'take_away' => 0,
            'delivery' => 1,
            'reviews_section' => 1,
            'pos_system' => 0,
            'active' => 1,
            'featured' => 0,
            'store_business_model' => 'none',
        ];
    }
}

