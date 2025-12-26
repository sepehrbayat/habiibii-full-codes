<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Beauty Staff API Update Request
 * درخواست به‌روزرسانی کارمند زیبایی (API)
 */
class BeautyStaffApiUpdateRequest extends FormRequest
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
        $staffId = $this->route('id');
        
        return [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('beauty_staff', 'email')->ignore($staffId),
            ],
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'nullable|boolean',
            'specializations' => 'nullable|array',
            'working_hours' => 'nullable|array',
            'breaks' => 'nullable|array',
            'days_off' => 'nullable|array',
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
            'name.max' => translate('messages.max_length_exceeded'),
            'email.email' => translate('messages.invalid_email'),
            'email.unique' => translate('messages.email_already_exists'),
            'phone.max' => translate('messages.max_length_exceeded'),
            'avatar.image' => translate('messages.invalid_image'),
            'avatar.mimes' => translate('messages.invalid_image_format'),
            'avatar.max' => translate('messages.image_size_exceeded'),
            'status.boolean' => translate('messages.invalid_status'),
            'specializations.array' => translate('messages.invalid_json'),
            'working_hours.array' => translate('messages.invalid_json'),
            'breaks.array' => translate('messages.invalid_json'),
            'days_off.array' => translate('messages.invalid_json'),
        ];
    }
}

