<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Salon Store Request
 * درخواست ایجاد سالن زیبایی
 */
class BeautySalonStoreRequest extends FormRequest
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
            'store_id' => 'required|integer|exists:stores,id',
            'zone_id' => 'nullable|integer|exists:zones,id',
            'business_type' => 'required|string|in:salon,clinic',
            'license_number' => 'nullable|string|max:100',
            'license_expiry' => 'nullable|date|after:today',
            'documents' => 'nullable|array',
            'documents.*' => 'string|max:500',
            'working_hours' => 'nullable|json',
            'holidays' => 'nullable|json',
            'is_featured' => 'nullable|boolean',
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
            'store_id.required' => translate('messages.required_field'),
            'store_id.exists' => translate('messages.not_found'),
            'zone_id.exists' => translate('messages.not_found'),
            'business_type.required' => translate('messages.required_field'),
            'business_type.in' => translate('messages.invalid_business_type'),
            'license_expiry.date' => translate('messages.invalid_date'),
            'license_expiry.after' => translate('messages.date_must_be_future'),
        ];
    }
}

