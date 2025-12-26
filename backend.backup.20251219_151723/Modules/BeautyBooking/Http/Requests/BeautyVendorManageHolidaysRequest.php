<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Vendor Manage Holidays Request
 * درخواست مدیریت تعطیلات فروشنده زیبایی
 */
class BeautyVendorManageHolidaysRequest extends FormRequest
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
            'action' => 'required|in:add,remove,replace',
            'holidays' => 'required|array',
            'holidays.*' => 'required|date|after_or_equal:today',
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
            'action.required' => translate('messages.required_field'),
            'action.in' => translate('messages.invalid_action'),
            'holidays.required' => translate('messages.required_field'),
            'holidays.array' => translate('messages.invalid_json'),
            'holidays.*.required' => translate('messages.required_field'),
            'holidays.*.date' => translate('messages.invalid_date'),
            'holidays.*.after_or_equal' => translate('messages.date_must_be_future'),
        ];
    }
}

