<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Vendor Profile Update Request
 * درخواست به‌روزرسانی پروفایل فروشنده زیبایی
 */
class BeautyVendorProfileUpdateRequest extends FormRequest
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
            'license_number' => 'nullable|string|max:100',
            'license_expiry' => 'nullable|date',
            'business_type' => 'nullable|in:salon,clinic',
            'working_hours' => 'nullable|array',
            'holidays' => 'nullable|array',
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
            'license_number.max' => translate('messages.max_length_exceeded'),
            'license_expiry.date' => translate('messages.invalid_date'),
            'business_type.in' => translate('messages.invalid_business_type'),
            'working_hours.array' => translate('messages.invalid_json'),
            'holidays.array' => translate('messages.invalid_json'),
        ];
    }
}

