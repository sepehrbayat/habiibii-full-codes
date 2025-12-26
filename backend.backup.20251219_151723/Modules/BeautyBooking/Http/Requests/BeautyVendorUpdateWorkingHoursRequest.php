<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Vendor Update Working Hours Request
 * درخواست به‌روزرسانی ساعات کاری فروشنده زیبایی
 */
class BeautyVendorUpdateWorkingHoursRequest extends FormRequest
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
            'working_hours' => 'required|array',
            'working_hours.*.day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'working_hours.*.open' => 'required|date_format:H:i',
            'working_hours.*.close' => 'required|date_format:H:i|after:working_hours.*.open',
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
            'working_hours.required' => translate('messages.required_field'),
            'working_hours.array' => translate('messages.invalid_json'),
            'working_hours.*.day.required' => translate('messages.required_field'),
            'working_hours.*.day.in' => translate('messages.invalid_day'),
            'working_hours.*.open.required' => translate('messages.required_field'),
            'working_hours.*.open.date_format' => translate('messages.invalid_time'),
            'working_hours.*.close.required' => translate('messages.required_field'),
            'working_hours.*.close.date_format' => translate('messages.invalid_time'),
            'working_hours.*.close.after' => translate('messages.end_time_must_be_after_start_time'),
        ];
    }
}

