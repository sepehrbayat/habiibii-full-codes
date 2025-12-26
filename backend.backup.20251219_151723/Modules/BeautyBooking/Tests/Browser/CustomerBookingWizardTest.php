<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyService;
use Tests\DuskTestCase;

/**
 * Customer Booking Wizard Browser Test
 * تست مرورگر جادوگر رزرو مشتری
 *
 * Tests customer booking wizard flow
 * تست جریان جادوگر رزرو مشتری
 */
class CustomerBookingWizardTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test booking wizard flow
     * تست جریان جادوگر رزرو
     *
     * @return void
     */
    public function test_booking_wizard_flow(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);
        
        $salon = BeautySalon::factory()->create([
            'verification_status' => 1,
            'is_verified' => true,
        ]);
        
        $service = BeautyService::factory()->create([
            'salon_id' => $salon->id,
            'status' => 1,
        ]);
        
        $this->browse(function ($browser) use ($user, $salon, $service) {
            $browser->loginAs($user)
                ->visit('/beauty-booking/salon/' . $salon->id)
                ->click('@book-now-button')
                ->assertSee('Select Service')
                ->click('@service-' . $service->id)
                ->click('@next-step')
                ->assertSee('Select Time')
                ->click('@time-slot-10-00')
                ->click('@next-step')
                ->assertSee('Payment')
                ->select('payment_method', 'cash_payment')
                ->click('@confirm-booking')
                ->waitForText('Booking confirmed')
                ->assertSee('Booking Reference');
        });
    }
}

