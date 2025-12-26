<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Package Store Request
 * درخواست ایجاد پکیج
 */
class BeautyPackageStoreRequest extends FormRequest
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
            'salon_id' => 'required|integer|exists:beauty_salons,id',
            'service_id' => 'required|integer|exists:beauty_services,id',
            'name' => 'required|string|max:255',
            'sessions_count' => 'required|integer|min:2|max:20',
            'total_price' => 'required|numeric|min:0|max:999999.99',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'validity_days' => 'required|integer|min:1|max:365',
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
            'salon_id.required' => translate('messages.required_field'),
            'salon_id.exists' => translate('messages.not_found'),
            'service_id.required' => translate('messages.required_field'),
            'service_id.exists' => translate('messages.not_found'),
            'name.required' => translate('messages.required_field'),
            'name.max' => translate('messages.max_length_exceeded'),
            'sessions_count.required' => translate('messages.required_field'),
            'sessions_count.min' => translate('messages.min_sessions_required'),
            'sessions_count.max' => translate('messages.max_sessions_exceeded'),
            'total_price.required' => translate('messages.required_field'),
            'total_price.numeric' => translate('messages.invalid_amount'),
            'discount_percentage.required' => translate('messages.required_field'),
            'discount_percentage.max' => translate('messages.max_discount_exceeded'),
            'validity_days.required' => translate('messages.required_field'),
            'validity_days.min' => translate('messages.min_validity_days'),
            'validity_days.max' => translate('messages.max_validity_days'),
        ];
    }
}

