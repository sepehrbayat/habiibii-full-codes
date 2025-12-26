<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Database\Factories;

use Modules\BeautyBooking\Entities\BeautySalon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Beauty Salon Factory
 * Factory برای سالن زیبایی
 */
class BeautySalonFactory extends Factory
{
    protected $model = BeautySalon::class;

    public function definition(): array
    {
        return [
            'store_id' => \App\Models\Store::factory(),
            'zone_id' => null,
            'business_type' => $this->faker->randomElement(['salon', 'clinic']),
            'license_number' => $this->faker->unique()->numerify('LIC-####'),
            'license_expiry' => $this->faker->dateTimeBetween('+1 year', '+5 years'),
            'documents' => null,
            'verification_status' => 1,
            'verification_notes' => null,
            'is_verified' => true,
            'is_featured' => false,
            'working_hours' => [
                'monday' => ['open' => '09:00', 'close' => '18:00'],
                'tuesday' => ['open' => '09:00', 'close' => '18:00'],
                'wednesday' => ['open' => '09:00', 'close' => '18:00'],
                'thursday' => ['open' => '09:00', 'close' => '18:00'],
                'friday' => ['open' => '09:00', 'close' => '18:00'],
                'saturday' => ['open' => '09:00', 'close' => '18:00'],
                'sunday' => ['open' => '10:00', 'close' => '16:00'],
            ],
            'holidays' => null,
            'avg_rating' => $this->faker->randomFloat(2, 3.5, 5.0),
            'total_bookings' => 0,
            'total_reviews' => 0,
            'total_cancellations' => 0,
            'cancellation_rate' => 0.0,
        ];
    }
}

