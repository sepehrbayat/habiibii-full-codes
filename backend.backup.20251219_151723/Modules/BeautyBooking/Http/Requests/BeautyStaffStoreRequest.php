<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Beauty Staff Store Request
 * درخواست ثبت کارمند
 */
class BeautyStaffStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:beauty_staff,email',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'boolean',
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
            'name.required' => translate('messages.required_field'),
            'email.email' => translate('messages.invalid_email'),
            'email.unique' => translate('messages.email_already_exists'),
            'avatar.image' => translate('messages.invalid_image'),
            'avatar.max' => translate('messages.image_size_exceeded'),
        ];
    }
}

