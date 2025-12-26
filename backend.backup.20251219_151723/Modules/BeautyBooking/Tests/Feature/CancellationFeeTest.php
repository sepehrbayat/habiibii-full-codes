<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Carbon\Carbon;

/**
 * Cancellation Fee Test
 * تست جریمه لغو
 *
 * Tests cancellation fee calculations
 * تست محاسبات جریمه لغو
 */
class CancellationFeeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test no fee for early cancellation
     * تست عدم جریمه برای لغو زودهنگام
     *
     * @return void
     */
    public function test_no_fee_for_early_cancellation(): void
    {
        $booking = BeautyBooking::factory()->create([
            'booking_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'booking_time' => '10:00',
            'booking_date_time' => Carbon::now()->addDays(2)->setTime(10, 0),
            'total_amount' => 100000,
        ]);
        
        $fee = $booking->calculateCancellationFee();
        
        $this->assertEquals(0, $fee);
    }

    /**
     * Test partial fee for late cancellation
     * تست جریمه جزئی برای لغو دیرهنگام
     *
     * @return void
     */
    public function test_partial_fee_for_late_cancellation(): void
    {
        $booking = BeautyBooking::factory()->create([
            'booking_date' => Carbon::now()->addHours(12)->format('Y-m-d'),
            'booking_time' => Carbon::now()->addHours(12)->format('H:i'),
            'booking_date_time' => Carbon::now()->addHours(12),
            'total_amount' => 100000,
        ]);
        
        $fee = $booking->calculateCancellationFee();
        
        $this->assertGreaterThan(0, $fee);
        $this->assertLessThan($booking->total_amount, $fee);
    }
}

