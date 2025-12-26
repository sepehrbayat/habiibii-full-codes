<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\BusinessSetting;
use App\CentralLogics\Helpers;

/**
 * Salon Registration Email
 * ایمیل ثبت‌نام سالن
 */
class SalonRegistration extends Mailable
{
    use Queueable, SerializesModels;

    protected $salonName;

    /**
     * Create a new message instance.
     * ایجاد یک نمونه پیام جدید
     *
     * @param string $salonName
     */
    public function __construct(string $salonName)
    {
        $this->salonName = $salonName;
    }

    /**
     * Build the message.
     * ساخت پیام
     *
     * @return $this
     */
    public function build()
    {
        $companyName = BusinessSetting::where('key', 'business_name')->first()->value ?? '';
        $storeName = $this->salonName;

        return $this->subject(translate('Salon_Registration_Confirmation'))
            ->view('email-templates.beautybooking.salon-registration', [
                'company_name' => $companyName,
                'store_name' => $storeName,
            ]);
    }
}

