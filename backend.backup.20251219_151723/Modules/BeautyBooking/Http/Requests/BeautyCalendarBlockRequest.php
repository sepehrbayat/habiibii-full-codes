<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Calendar Block Request
 * درخواست ایجاد بلاک تقویم
 */
class BeautyCalendarBlockRequest extends FormRequest
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
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'type' => 'required|in:break,holiday,manual_block',
            'reason' => 'nullable|string|max:500',
            'staff_id' => 'nullable|integer|exists:beauty_staff,id',
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
            'date.required' => translate('messages.required_field'),
            'date.date' => translate('messages.invalid_date'),
            'start_time.required' => translate('messages.required_field'),
            'start_time.date_format' => translate('messages.invalid_time'),
            'end_time.required' => translate('messages.required_field'),
            'end_time.date_format' => translate('messages.invalid_time'),
            'end_time.after' => translate('messages.end_time_must_be_after_start_time'),
            'type.required' => translate('messages.required_field'),
            'type.in' => translate('messages.invalid_block_type'),
        ];
    }
}

