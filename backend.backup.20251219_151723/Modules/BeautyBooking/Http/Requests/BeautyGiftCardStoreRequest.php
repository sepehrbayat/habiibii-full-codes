<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Gift Card Store Request
 * درخواست ایجاد کارت هدیه
 */
class BeautyGiftCardStoreRequest extends FormRequest
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
            'salon_id' => 'nullable|integer|exists:beauty_salons,id',
            'purchased_by' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:10000|max:1000000',
            'expires_at' => 'nullable|date|after:today',
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
            'purchased_by.required' => translate('messages.required_field'),
            'purchased_by.exists' => translate('messages.not_found'),
            'salon_id.exists' => translate('messages.not_found'),
            'amount.required' => translate('messages.required_field'),
            'amount.numeric' => translate('messages.invalid_amount'),
            'amount.min' => translate('messages.min_gift_card_amount'),
            'amount.max' => translate('messages.max_gift_card_amount'),
            'expires_at.date' => translate('messages.invalid_date'),
            'expires_at.after' => translate('messages.date_must_be_future'),
        ];
    }
}

