<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Package Update Request
 * درخواست به‌روزرسانی پکیج
 */
class BeautyPackageUpdateRequest extends FormRequest
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
            'service_id' => 'nullable|integer|exists:beauty_services,id',
            'name' => 'nullable|string|max:255',
            'sessions_count' => 'nullable|integer|min:2|max:20',
            'total_price' => 'nullable|numeric|min:0|max:999999.99',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'validity_days' => 'nullable|integer|min:1|max:365',
            'status' => 'nullable|boolean',
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
            'salon_id.exists' => translate('messages.not_found'),
            'service_id.exists' => translate('messages.not_found'),
            'name.max' => translate('messages.max_length_exceeded'),
            'sessions_count.min' => translate('messages.min_sessions_required'),
            'sessions_count.max' => translate('messages.max_sessions_exceeded'),
            'total_price.numeric' => translate('messages.invalid_amount'),
            'discount_percentage.max' => translate('messages.max_discount_exceeded'),
            'validity_days.min' => translate('messages.min_validity_days'),
            'validity_days.max' => translate('messages.max_validity_days'),
        ];
    }
}

