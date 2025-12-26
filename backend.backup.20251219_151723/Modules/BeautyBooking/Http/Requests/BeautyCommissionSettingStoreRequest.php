<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Commission Setting Store Request
 * درخواست ایجاد تنظیمات کمیسیون
 */
class BeautyCommissionSettingStoreRequest extends FormRequest
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
            'service_category_id' => 'nullable|integer|exists:beauty_service_categories,id',
            'salon_level' => 'nullable|string|in:salon,clinic',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'min_commission' => 'nullable|numeric|min:0|max:999999.99',
            'max_commission' => 'nullable|numeric|min:0|max:999999.99',
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
            'service_category_id.exists' => translate('messages.not_found'),
            'salon_level.in' => translate('messages.invalid_salon_level'),
            'commission_percentage.required' => translate('messages.required_field'),
            'commission_percentage.numeric' => translate('messages.invalid_amount'),
            'commission_percentage.min' => translate('messages.min_commission'),
            'commission_percentage.max' => translate('messages.max_commission'),
            'min_commission.numeric' => translate('messages.invalid_amount'),
            'max_commission.numeric' => translate('messages.invalid_amount'),
        ];
    }
}

