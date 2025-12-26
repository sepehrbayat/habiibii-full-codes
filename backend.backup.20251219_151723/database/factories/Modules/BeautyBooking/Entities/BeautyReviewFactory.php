<?php

namespace Database\Factories\Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BeautyBooking\Entities\BeautyReview;

class BeautyReviewFactory extends Factory
{
    protected $model = BeautyReview::class;

    public function definition(): array
    {
        return [
            'booking_id' => \Modules\BeautyBooking\Entities\BeautyBooking::factory(),
            'user_id' => \App\Models\User::factory(),
            'salon_id' => \Modules\BeautyBooking\Entities\BeautySalon::factory(),
            'service_id' => \Modules\BeautyBooking\Entities\BeautyService::factory(),
            'staff_id' => null,
            'rating' => 5,
            'comment' => $this->faker->sentence(),
            'attachments' => [],
            'status' => 'approved',
            'admin_notes' => null,
        ];
    }
}

