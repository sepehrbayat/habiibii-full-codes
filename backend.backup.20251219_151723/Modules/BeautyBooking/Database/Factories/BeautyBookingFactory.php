<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Database\Factories;

use Modules\BeautyBooking\Entities\BeautyBooking;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * Beauty Booking Factory
 * Factory برای رزروها
 */
class BeautyBookingFactory extends Factory
{
    protected $model = BeautyBooking::class;

    public function definition(): array
    {
        // Support both past and future bookings for testing
        // پشتیبانی از رزروهای گذشته و آینده برای تست
        $daysOffset = $this->faker->numberBetween(-30, 30); // Past 30 days to future 30 days
        $bookingDate = Carbon::now()->addDays($daysOffset);
        $bookingTime = $this->faker->time('H:i');
        $bookingDateTime = Carbon::parse($bookingDate->format('Y-m-d') . ' ' . $bookingTime);

        return [
            'user_id' => \App\Models\User::factory(),
            'salon_id' => \Modules\BeautyBooking\Entities\BeautySalon::factory(),
            'service_id' => \Modules\BeautyBooking\Entities\BeautyService::factory(),
            'staff_id' => null,
            'zone_id' => null,
            'package_id' => null,
            'main_service_id' => null,
            'conversation_id' => null,
            'booking_date' => $bookingDate,
            'booking_time' => $bookingTime,
            'booking_date_time' => $bookingDateTime,
            'booking_reference' => 'BK-' . $this->faker->unique()->numerify('######'),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cash_payment',
            'total_amount' => $this->faker->randomFloat(2, 100, 1000),
            'commission_amount' => 0.0,
            'service_fee' => 0.0,
            'cancellation_fee' => 0.0,
            'consultation_credit_percentage' => 0.0,
            'consultation_credit_amount' => 0.0,
            'additional_services' => null,
            'notes' => null,
            'cancellation_reason' => null,
            'cancelled_by' => 'none',
        ];
    }
}

