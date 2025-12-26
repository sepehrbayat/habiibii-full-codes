<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Booking Refund Request
 * درخواست بازپرداخت رزرو زیبایی
 */
class BeautyBookingRefundRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * تعیین اینکه آیا کاربر مجاز به انجام این درخواست است
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * دریافت قوانین اعتبارسنجی اعمال شده به درخواست
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'refund_amount' => 'required|numeric|min:0',
            'refund_reason' => 'required|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     * دریافت پیام‌های سفارشی برای خطاهای اعتبارسنجی
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'refund_amount.required' => translate('messages.required_field'),
            'refund_amount.numeric' => translate('messages.invalid_amount'),
            'refund_amount.min' => translate('messages.invalid_amount'),
            'refund_reason.required' => translate('messages.required_field'),
            'refund_reason.max' => translate('messages.max_length_exceeded'),
        ];
    }
}

