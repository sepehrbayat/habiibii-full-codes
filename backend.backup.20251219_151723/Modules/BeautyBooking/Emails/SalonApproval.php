<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\BusinessSetting;
use App\CentralLogics\Helpers;

/**
 * Salon Approval Email
 * ایمیل تأیید سالن
 */
class SalonApproval extends Mailable
{
    use Queueable, SerializesModels;

    protected $salonName;
    protected $status;
    protected $notes;

    /**
     * Create a new message instance.
     * ایجاد یک نمونه پیام جدید
     *
     * @param string $salonName
     * @param string $status approved or rejected
     * @param string|null $notes
     */
    public function __construct(string $salonName, string $status, ?string $notes = null)
    {
        $this->salonName = $salonName;
        $this->status = $status;
        $this->notes = $notes;
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
        $subject = $this->status === 'approved' 
            ? translate('Salon_Approval_Notification')
            : translate('Salon_Rejection_Notification');

        return $this->subject($subject)
            ->view('email-templates.beautybooking.salon-approval', [
                'company_name' => $companyName,
                'store_name' => $storeName,
                'status' => $this->status,
                'notes' => $this->notes,
            ]);
    }
}

