<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\BeautyBooking\Entities\BeautyBooking;
use App\Models\BusinessSetting;
use App\CentralLogics\Helpers;

/**
 * Booking Confirmation Email
 * ایمیل تأیید رزرو
 */
class BookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    protected $bookingId;

    /**
     * Create a new message instance.
     * ایجاد یک نمونه پیام جدید
     *
     * @param int $bookingId
     */
    public function __construct(int $bookingId)
    {
        $this->bookingId = $bookingId;
    }

    /**
     * Build the message.
     * ساخت پیام
     *
     * @return $this
     */
    public function build()
    {
        $booking = BeautyBooking::with(['user', 'salon.store', 'service', 'staff'])->findOrFail($this->bookingId);
        $companyName = BusinessSetting::where('key', 'business_name')->first()->value ?? '';
        $userName = ($booking->user->f_name ?? '') . ' ' . ($booking->user->l_name ?? '');
        $salonName = $booking->salon->store->name ?? '';

        return $this->subject(translate('Booking_Confirmation'))
            ->view('email-templates.beautybooking.booking-confirmation', [
                'company_name' => $companyName,
                'booking' => $booking,
                'user_name' => $userName,
                'salon_name' => $salonName,
            ]);
    }
}

